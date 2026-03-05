#!/usr/bin/env python3
"""
mesh_render.py — Download, decode, and render one MRMS MESH GRIB2 frame.

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
    from PIL import Image
    import requests
except ImportError as e:
    print(f"Missing dependency: {e}", file=sys.stderr)
    sys.exit(1)

# ── MRMS MESH CONUS grid spec ─────────────────────────────────────────────────
# 3500 rows × 7000 columns, 0.01° spacing
# Row 0 = 55.005°N (north edge), last row = 20.005°N (south edge)
# Col 0 = -129.995°W (west edge), last col = -60.005°W (east edge)
GRID_NJ = 3500
GRID_NI = 7000

# Missing value sentinel in MRMS GRIB2 (9999.0 mm)
MISSING_THRESHOLD_MM = 9000.0


# ── Color scale (hail size in inches → RGBA) ──────────────────────────────────

def apply_colormap(grid_inches):
    """
    Vectorised colormap over a 2-D numpy float32 array.
    Returns uint8 RGBA array of same spatial shape.
    """
    h, w = grid_inches.shape
    r = np.zeros((h, w), dtype=np.uint8)
    g = np.zeros((h, w), dtype=np.uint8)
    b = np.zeros((h, w), dtype=np.uint8)
    a = np.zeros((h, w), dtype=np.uint8)

    # Green  0.5 – 1.0"
    m = (grid_inches >= 0.50) & (grid_inches < 1.00)
    r[m] = 0;   g[m] = 200; b[m] = 0;   a[m] = 190

    # Yellow 1.0 – 1.75"
    m = (grid_inches >= 1.00) & (grid_inches < 1.75)
    r[m] = 220; g[m] = 220; b[m] = 0;   a[m] = 210

    # Orange 1.75 – 2.5"
    m = (grid_inches >= 1.75) & (grid_inches < 2.50)
    r[m] = 255; g[m] = 130; b[m] = 0;   a[m] = 220

    # Red    2.5 – 3.0"
    m = (grid_inches >= 2.50) & (grid_inches < 3.00)
    r[m] = 220; g[m] = 0;   b[m] = 0;   a[m] = 230

    # Purple ≥ 3.0" (extreme)
    m = grid_inches >= 3.00
    r[m] = 180; g[m] = 0;   b[m] = 220; a[m] = 240

    return np.stack([r, g, b, a], axis=-1)


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
    MRMS MESH values are stored in mm; we convert to inches.
    Immediately downsamples via max-pool to reduce memory footprint.
    Returns float32 numpy array (GRID_NJ//downsample, GRID_NI//downsample) in inches.
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
                # Zero out missing / fill values
                grid_mm[grid_mm > MISSING_THRESHOLD_MM] = 0.0
                grid_mm[grid_mm < 0.0] = 0.0
                # mm → inches
                grid_in = grid_mm / 25.4

                # Immediately max-pool downsample to keep memory manageable
                # Full grid: 3500×7000 × 4 bytes = ~94 MB
                # Downsampled: 350×700 × 4 bytes = ~980 KB
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
    Saves updated accumulator to `acc_path`.
    Returns the updated grid (for rendering).
    """
    if os.path.exists(acc_path):
        existing = np.load(acc_path)
        grid_in = np.maximum(grid_in, existing)
    np.save(acc_path, grid_in)
    return grid_in


# ── Render PNG ────────────────────────────────────────────────────────────────

def render_png(grid_in, output_path):
    """
    Render already-downsampled MESH grid to RGBA PNG.
    grid_in should already be at the desired output resolution.
    """
    rgba = apply_colormap(grid_in)
    img = Image.fromarray(rgba, 'RGBA')
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    img.save(output_path, 'PNG', optimize=True)
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

        # 3. Decode grid (already downsampled to ~350×700 inside this call)
        grid_in = read_mesh_grid(grib2_path, downsample=args.downsample)

        # 4. Update accumulator (daily max at downsampled resolution: ~980 KB/day)
        if args.accumulator:
            grid_in = update_accumulator(grid_in, args.accumulator)

        # 5. Render PNG
        render_png(grid_in, args.output)

    print(f"OK: {args.output}")


if __name__ == '__main__':
    main()
