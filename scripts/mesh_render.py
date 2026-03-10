#!/usr/bin/env python3
"""
mesh_render.py — Download, decode, and render one MRMS MESH GRIB2 frame.

Renders as a per-pixel RGBA PNG using Pillow. Each grid cell above the
minimum threshold is painted with its size-band color. This correctly
renders even isolated single-pixel hail areas (contourf cannot).

Dependencies: eccodes, numpy, Pillow, requests  (no matplotlib needed)

Usage:
    python3.8 mesh_render.py --url <grib2_gz_url> --output /path/to/frame.png
                             [--accumulator /path/to/daily_max.npy]
                             [--downsample 10]

Exits 0 on success, writes "OK: <output_path>" to stdout.
Exits non-zero on error, writes error to stderr.
"""

import sys
import os
import gzip
import tempfile
import argparse

try:
    import eccodes
    import numpy as np
    import requests
    from PIL import Image
except ImportError as e:
    print(f"Missing dependency: {e}", file=sys.stderr)
    sys.exit(1)

# ── MRMS MESH CONUS grid spec ─────────────────────────────────────────────────
# 3500 rows × 7000 columns, 0.01° spacing
# Row 0 = 55.005°N (north edge), last row = 20.005°N (south edge)
# Col 0 = −129.995°W (west edge), last col = −60.005°W (east edge)
GRID_NJ = 3500
GRID_NI = 7000

# Missing value sentinel in MRMS GRIB2 (9999.0 mm)
MISSING_THRESHOLD_MM = 9000.0

# ── Output dimensions — must match Leaflet overlay aspect ratio (2:1 CONUS) ───
# Grid is 350×700 after 10× downsample; output is the same size (1 px per cell)
OUT_W = 700   # pixels
OUT_H = 350   # pixels

# ── Size bands: lower bound (inches), RGBA color ──────────────────────────────
# Bands are applied in order; each pixel gets the color of its highest bracket.
# Alpha 220 ≈ 86% opacity (0.86 × 255).
ALPHA = 220

BANDS = [
    (0.75,  (255, 255, 178, ALPHA)),  # 0.75–1.00"  pale yellow
    (1.00,  (255, 255,   0, ALPHA)),  # 1.00–1.25"  yellow
    (1.25,  (255, 215,   0, ALPHA)),  # 1.25–1.50"  gold
    (1.50,  (255, 165,   0, ALPHA)),  # 1.50–1.75"  orange
    (1.75,  (255, 127,   0, ALPHA)),  # 1.75–2.00"  dark orange
    (2.00,  (255,  69,   0, ALPHA)),  # 2.00–2.25"  orange-red
    (2.25,  (255,   0,   0, ALPHA)),  # 2.25–2.50"  red
    (2.50,  (204,   0,   0, ALPHA)),  # 2.50–2.75"  dark red
    (2.75,  (153,   0,   0, ALPHA)),  # 2.75–3.00"  deep red
    (3.00,  (102,   0,   0, ALPHA)),  # 3.00–3.50"  maroon
    (3.50,  ( 68,   0,  34, ALPHA)),  # 3.50–4.00"  near-black red
    (4.00,  ( 34,   0,  51, ALPHA)),  # 4.00"+       very dark purple
]


# ── Download ──────────────────────────────────────────────────────────────────

def download_file(url, dest_path):
    resp = requests.get(url, timeout=45, stream=True)
    resp.raise_for_status()
    with open(dest_path, 'wb') as f:
        for chunk in resp.iter_content(chunk_size=65536):
            f.write(chunk)


# ── GRIB2 decode ──────────────────────────────────────────────────────────────

def read_mesh_grid(grib2_path, downsample=10):
    """
    Read the first message whose value array matches GRID_NJ × GRID_NI.
    MRMS MESH values are stored in mm; converted to inches.
    Max-pool downsamples immediately to reduce memory footprint.
    Returns float32 numpy array (GRID_NJ//downsample, GRID_NI//downsample).
    """
    with open(grib2_path, 'rb') as f:
        while True:
            try:
                msg = eccodes.codes_grib_new_from_file(f)
            except Exception:
                break
            if msg is None:
                break
            try:
                vals = eccodes.codes_get_values(msg)
            finally:
                eccodes.codes_release(msg)

            if len(vals) == GRID_NJ * GRID_NI:
                grid_mm = np.array(vals, dtype=np.float32).reshape(GRID_NJ, GRID_NI)
                grid_mm[grid_mm > MISSING_THRESHOLD_MM] = 0.0
                grid_mm[grid_mm < 0.0] = 0.0
                grid_in = grid_mm / 25.4

                # Max-pool downsample: 3500×7000 → 350×700
                nj, ni = grid_in.shape
                oh = nj // downsample
                ow = ni // downsample
                grid_in = grid_in[:oh * downsample, :ow * downsample]
                grid_in = grid_in.reshape(oh, downsample, ow, downsample).max(axis=(1, 3))
                return grid_in

    raise ValueError(
        f"No {GRID_NJ}×{GRID_NI} MESH grid found in {grib2_path}. "
        "File may be corrupt or use an unexpected grid."
    )


# ── Accumulator (daily max) ────────────────────────────────────────────────────

def update_accumulator(grid_in, acc_path):
    """
    Pixel-wise max of `grid_in` and the previously stored accumulator.
    Saves updated accumulator to `acc_path`. Returns updated grid.
    """
    if os.path.exists(acc_path):
        existing = np.load(acc_path)
        grid_in = np.maximum(grid_in, existing)
    np.save(acc_path, grid_in)
    return grid_in


# ── Render PNG ────────────────────────────────────────────────────────────────

def render_png(grid_in, output_path):
    """
    Render MESH grid as a smooth transparent RGBA PNG using Pillow.

    Uses premultiplied-alpha Gaussian blur: multiply RGB by alpha before
    blurring, blur all channels, then de-premultiply. This prevents the
    black/grey bleeding that occurs when transparent (0,0,0,0) pixels are
    naively mixed with colored pixels during a standard RGBA blur.

    Grid orientation: row 0 = north (55°N), col 0 = west (−130°W).
    Leaflet imageOverlay stretches from SW→NE, so row 0 must be at the TOP.
    """
    from PIL import ImageFilter

    h, w = grid_in.shape

    # Build straight-alpha RGBA
    rgba = np.zeros((h, w, 4), dtype=np.uint8)
    for threshold, color in BANDS:
        rgba[grid_in >= threshold] = color

    # ── Premultiplied-alpha Gaussian blur ─────────────────────────────────────
    # Blurring in straight-alpha space causes transparent (black) pixels to
    # bleed grey halos into colored areas. Premultiplied-alpha avoids this.

    def _blur_channel(arr_f, radius):
        """Blur a float32 2D array via PIL GaussianBlur, returning float32."""
        img_l = Image.fromarray(np.clip(arr_f, 0, 255).astype(np.uint8), 'L')
        return np.array(img_l.filter(ImageFilter.GaussianBlur(radius=radius)),
                        dtype=np.float32)

    arr  = rgba.astype(np.float32)
    a_f  = arr[:, :, 3] / 255.0  # normalized alpha

    # Premultiply RGB by alpha so transparent pixels contribute no color
    r_pre = arr[:, :, 0] * a_f
    g_pre = arr[:, :, 1] * a_f
    b_pre = arr[:, :, 2] * a_f
    a_pre = arr[:, :, 3]         # alpha blurred in straight space is fine

    radius = 1.5
    r_b = _blur_channel(r_pre, radius)
    g_b = _blur_channel(g_pre, radius)
    b_b = _blur_channel(b_pre, radius)
    a_b = _blur_channel(a_pre, radius)

    # De-premultiply: recover straight-alpha RGB
    a_n   = a_b / 255.0
    eps   = 1e-6
    r_out = np.where(a_n > eps, r_b / np.maximum(a_n, eps), 0.0)
    g_out = np.where(a_n > eps, g_b / np.maximum(a_n, eps), 0.0)
    b_out = np.where(a_n > eps, b_b / np.maximum(a_n, eps), 0.0)

    out = np.stack([r_out, g_out, b_out, a_b], axis=2)

    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    Image.fromarray(np.clip(out, 0, 255).astype(np.uint8), 'RGBA').save(
        output_path, format='PNG')

    return output_path


# ── Entry point ────────────────────────────────────────────────────────────────

def main():
    parser = argparse.ArgumentParser(description='Render MRMS MESH GRIB2 frame to PNG')
    parser.add_argument('--url',         required=True,  help='URL of .grib2.gz file')
    parser.add_argument('--output',      required=True,  help='Output PNG path')
    parser.add_argument('--accumulator', default=None,   help='Daily max .npy accumulator path')
    parser.add_argument('--downsample',  type=int, default=10, help='Spatial downsample factor (default 10)')
    args = parser.parse_args()

    with tempfile.TemporaryDirectory() as tmpdir:
        gz_path    = os.path.join(tmpdir, 'mesh.grib2.gz')
        grib2_path = os.path.join(tmpdir, 'mesh.grib2')

        # 1. Download
        download_file(args.url, gz_path)

        # 2. Decompress
        with gzip.open(gz_path, 'rb') as gz_f, open(grib2_path, 'wb') as out_f:
            out_f.write(gz_f.read())

        # 3. Decode + downsample
        grid_in = read_mesh_grid(grib2_path, downsample=args.downsample)

        # 4. Update daily-max accumulator
        if args.accumulator:
            grid_in = update_accumulator(grid_in, args.accumulator)

        # 5. Render contour PNG
        render_png(grid_in, args.output)

        # 6. Write peak-value metadata JSON for PHP to read
        import json  # stdlib, always available
        meta_path = os.path.splitext(args.output)[0] + '.json'
        with open(meta_path, 'w') as mf:
            json.dump({'max_inches': round(float(grid_in.max()), 3)}, mf)

        # 7. Write sparse cell data JSON for frontend click/hover lookup.
        # Stores row/col (integers, compact) + value for every cell >= minimum
        # threshold. JS converts r/c back to lat/lng using the same grid spec.
        rows_idx, cols_idx = np.where(grid_in >= 0.75)
        cells = [
            {'r': int(r), 'c': int(c), 'v': round(float(grid_in[r, c]), 2)}
            for r, c in zip(rows_idx, cols_idx)
        ]
        data_path = os.path.join(os.path.dirname(args.output), 'data.json')
        with open(data_path, 'w') as df:
            json.dump(cells, df, separators=(',', ':'))

    print(f"OK: {args.output}")


if __name__ == '__main__':
    main()
