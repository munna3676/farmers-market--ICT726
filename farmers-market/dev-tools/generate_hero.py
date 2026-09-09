from PIL import Image, ImageDraw, ImageFilter
import math

OUT = "/home/claude/farmers-market"
W, H = 1600, 700
SS = 2

def vertical_gradient(size, top, bottom):
    w, h = size
    base = Image.new('RGB', (1, h), color=0)
    draw = ImageDraw.Draw(base)
    for y in range(h):
        t = y / max(h - 1, 1)
        r = int(top[0] + (bottom[0] - top[0]) * t)
        g = int(top[1] + (bottom[1] - top[1]) * t)
        b = int(top[2] + (bottom[2] - top[2]) * t)
        draw.point((0, y), fill=(r, g, b))
    return base.resize((w, h))

# Sky gradient: warm dawn tone fading into farm green
bg = vertical_gradient((W*SS, H*SS), (255, 214, 153), (168, 209, 140)).convert('RGBA')
draw = ImageDraw.Draw(bg)

# Sun
sun_x, sun_y, sun_r = int(W*SS*0.82), int(H*SS*0.22), int(120*SS)
draw.ellipse([sun_x-sun_r, sun_y-sun_r, sun_x+sun_r, sun_y+sun_r], fill=(255, 236, 179, 255))
draw.ellipse([sun_x-sun_r*0.7, sun_y-sun_r*0.7, sun_x+sun_r*0.7, sun_y+sun_r*0.7], fill=(255, 224, 140, 255))

# Rolling hills (layered, back to front)
hill_colors = [(139, 176, 106, 255), (108, 150, 84, 255), (74, 124, 62, 255)]
hill_base_y = [0.62, 0.74, 0.88]
for i, color in enumerate(hill_colors):
    pts = [(0, H*SS)]
    base_y = hill_base_y[i] * H * SS
    n = 6
    for j in range(n+1):
        x = j * (W*SS/n)
        wobble = math.sin(j * 1.3 + i) * 40 * SS
        pts.append((x, base_y + wobble))
    pts.append((W*SS, H*SS))
    draw.polygon(pts, fill=color)

# Simple rows/furrows on the front hill (farmland texture)
for i in range(14):
    y = int(H*SS*0.90) + i * int(14*SS)
    if y > H*SS:
        break
    draw.line([(0, y), (W*SS, y - 20*SS)], fill=(64, 108, 52, 120), width=int(3*SS))

# A few simple tree/shrub silhouettes
import random
random.seed(5)
for i in range(9):
    tx = random.uniform(0.05, 0.95) * W * SS
    ty = (0.66 + random.uniform(-0.02, 0.05)) * H * SS
    tr = random.uniform(18, 34) * SS
    draw.ellipse([tx-tr, ty-tr*1.1, tx+tr, ty+tr*0.3], fill=(63, 110, 55, 255))
    draw.rectangle([tx-3*SS, ty, tx+3*SS, ty+16*SS], fill=(90, 65, 40, 255))

bg = bg.filter(ImageFilter.GaussianBlur(0.6))
final = bg.resize((W, H), Image.LANCZOS).convert('RGB')
final.save(f"{OUT}/assets/images/hero-farm.jpg", quality=88)
print("Hero banner generated.")
