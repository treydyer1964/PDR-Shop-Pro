#!/usr/bin/env python3
"""
mesh_render.py — Download, decode, and render one MRMS MESH GRIB2 frame.

Renders as filled contours (matplotlib contourf) with size bands matching
HailPoint's legend: 0.75" through 4.00"+. Transparent PNG — no axes/borders.

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

# Must set backend before importing pyplot — server has no display
import matplotlib
matplotlib.use('Agg')

try:
    import eccodes
    import numpy as np
    import requests
    import matplotlib.pyplot as plt
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
OUT_W = 700   # pixels
OUT_H = 350   # pixels
DPI   = 100   # → figure is 7" × 3.5" = exactly 700 × 350 px

# ── Contour levels (inches) and fill colors ───────────────────────────────────
# 13 boundary values → 12 filled bands, matching HailPoint's legend
CONTOUR_LEVELS = [0.75, 1.00, 1.25, 1.50, 1.75, 2.00, 2.25, 2.50, 2.75, 3.00, 3.50, 4.00, 10.0]
CONTOUR_COLORS = [
    '#FFFFB2',  # 0.75–1.00"  pale yellow
    '#FFFF00',  # 1.00–1.25"  yellow
    '#FFD700',  # 1.25–1.50"  gold
    '#FFA500',  # 1.50–1.75"  orange
    '#FF7F00',  # 1.75–2.00"  dark orange
    '#FF4500',  # 2.00–2.25"  orange-red
    '#FF0000',  # 2.25–2.50"  red
    '#CC0000',  # 2.50–2.75"  dark red
    '#990000',  # 2.75–3.00"  deep red
    '#660000',  # 3.00–3.50"  maroon
    '#440022',  # 3.50–4.00"  near-black red
    '#220033',  # 4.00"+      very dark purple
]

# Opacity of the filled contour overlay (0–1)
CONTOUR_ALPHA = 0.75


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
    Render MESH grid as filled contours with transparent background.

    Uses matplotlib contourf so size-band boundaries are smooth interpolated
    isolines (same visual style as HailPoint) rather than hard per-pixel edges.

    Grid orientation: row 0 = north (55°N), col 0 = west (−130°W).
    Leaflet imageOverlay stretches PNG from SW→NE, so row 0 must appear at
    the top of the image — achieved by flipping the Y axis limits.
    """
    h, w = grid_in.shape

    # Mask everything below the lowest threshold — renders transparent
    masked = np.ma.masked_where(grid_in < CONTOUR_LEVELS[0], grid_in)

    fig = plt.figure(figsize=(OUT_W / DPI, OUT_H / DPI), dpi=DPI)
    ax = fig.add_axes([0, 0, 1, 1])  # full bleed — zero margins
    ax.set_axis_off()
    ax.set_xlim(0, w)
    ax.set_ylim(h, 0)   # flip: Y=0 (north/row-0) at top of image

    if not np.all(masked.mask):
        x_idx = np.arange(w)
        y_idx = np.arange(h)
        X, Y = np.meshgrid(x_idx, y_idx)
        ax.contourf(
            X, Y, masked,
            levels=CONTOUR_LEVELS,
            colors=CONTOUR_COLORS,
            alpha=CONTOUR_ALPHA,
        )

    fig.patch.set_alpha(0)
    ax.patch.set_alpha(0)

    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    fig.savefig(output_path, dpi=DPI, transparent=True, format='png')
    plt.close(fig)

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
        import json
        meta_path = os.path.splitext(args.output)[0] + '.json'
        with open(meta_path, 'w') as mf:
            json.dump({'max_inches': round(float(grid_in.max()), 3)}, mf)

    print(f"OK: {args.output}")


if __name__ == '__main__':
    main()
