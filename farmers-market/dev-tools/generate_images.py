"""
Generates polished, flat-design illustration images for the demo products
and hero banner, since real farmer photos aren't available for a class demo.
Uses supersampling (draw at 2x, downscale) for smooth anti-aliased shapes.
"""
from PIL import Image, ImageDraw, ImageFilter
import math
import random

OUT = "/home/claude/farmers-market"

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

def soft_shadow(img, blur=18, offset=(0, 14), opacity=60):
    """Return an image with a soft shadow behind the drawn (non-transparent) content."""
    alpha = img.split()[-1]
    shadow = Image.new('RGBA', img.size, (0, 0, 0, 0))
    shadow_shape = Image.new('RGBA', img.size, (0, 0, 0, opacity))
    shadow.paste(shadow_shape, offset, alpha)
    shadow = shadow.filter(ImageFilter.GaussianBlur(blur))
    combined = Image.alpha_composite(shadow, img)
    return combined

SS = 3  # supersample factor

def new_canvas(w, h):
    return Image.new('RGBA', (w * SS, h * SS), (0, 0, 0, 0)), w, h

def finish(canvas, w, h):
    return canvas.resize((w, h), Image.LANCZOS)

W, H = 520, 390

# -------------------------------------------------------------
# 1. TOMATOES
# -------------------------------------------------------------
bg = vertical_gradient((W, H), (255, 214, 153), (255, 168, 120)).convert('RGBA')
draw_layer, w, h = new_canvas(W, H)
d = ImageDraw.Draw(draw_layer)
random.seed(3)
positions = [(0.30, 0.55, 95), (0.62, 0.45, 115), (0.48, 0.72, 80), (0.78, 0.68, 70)]
for (px, py, r) in positions:
    cx, cy = int(px * W * SS), int(py * H * SS)
    rr = int(r * SS)
    d.ellipse([cx-rr, cy-rr, cx+rr, cy+rr], fill=(196, 47, 39, 255))
    # highlight (opaque tint, see note above about PIL draw not blending)
    d.ellipse([cx-rr*0.45, cy-rr*0.55, cx+rr*0.05, cy-rr*0.05], fill=(226, 108, 95, 255))
    # calyx (star leaves)
    for i in range(5):
        ang = i * (360/5) + 90
        lx = cx + math.cos(math.radians(ang)) * rr * 0.35
        ly = cy - rr*0.9 + math.sin(math.radians(ang)) * rr * 0.35
        d.polygon([(cx, cy-rr*0.85), (lx-8*SS, ly-6*SS), (lx+8*SS, ly-6*SS)], fill=(74, 124, 62, 255))
draw_layer = draw_layer.filter(ImageFilter.GaussianBlur(1))
img = soft_shadow(draw_layer, blur=22, offset=(0, 20*SS), opacity=50)
img = finish(img, W, H)
final = Image.alpha_composite(bg, img).convert('RGB')
final.save(f"{OUT}/uploads/products/sample-tomatoes.jpg", quality=90)

# -------------------------------------------------------------
# 2. APPLES
# -------------------------------------------------------------
bg = vertical_gradient((W, H), (255, 235, 205), (250, 200, 170)).convert('RGBA')
draw_layer, w, h = new_canvas(W, H)
d = ImageDraw.Draw(draw_layer)
positions = [(0.32, 0.52, 100), (0.65, 0.42, 120), (0.50, 0.75, 85)]
colors = [(211, 47, 47, 255), (233, 90, 60, 255), (198, 40, 60, 255)]
for i, (px, py, r) in enumerate(positions):
    cx, cy = int(px * W * SS), int(py * H * SS)
    rr = int(r * SS)
    # apple body: two overlapping circles for the classic apple silhouette dip on top
    d.ellipse([cx-rr, cy-rr*0.9, cx+rr, cy+rr], fill=colors[i])
    d.ellipse([cx-rr*0.5, cy-rr*1.05, cx+rr*0.15, cy-rr*0.55], fill=colors[i])
    d.ellipse([cx-rr*0.1, cy-rr*1.05, cx+rr*0.55, cy-rr*0.55], fill=colors[i])
    # stem
    d.line([(cx+rr*0.1, cy-rr*0.95), (cx+rr*0.2, cy-rr*1.35)], fill=(90, 60, 30, 255), width=int(6*SS))
    # leaf
    d.ellipse([cx+rr*0.15, cy-rr*1.35, cx+rr*0.55, cy-rr*1.1], fill=(94, 148, 78, 255))
    # highlight (opaque light tint - PIL draw doesn't blend against shapes below it,
    # only against the final destination, so translucent fills here would pick up
    # the page background color instead of the apple's own red. Use an opaque tint instead.)
    tint = tuple(min(255, c + 55) for c in colors[i][:3]) + (255,)
    d.ellipse([cx-rr*0.5, cy-rr*0.4, cx-rr*0.12, cy], fill=tint)
draw_layer = draw_layer.filter(ImageFilter.GaussianBlur(1))
img = soft_shadow(draw_layer, blur=22, offset=(0, 20*SS), opacity=50)
img = finish(img, W, H)
final = Image.alpha_composite(bg, img).convert('RGB')
final.save(f"{OUT}/uploads/products/sample-apples.jpg", quality=90)

# -------------------------------------------------------------
# 3. FREE-RANGE EGGS
# -------------------------------------------------------------
bg = vertical_gradient((W, H), (222, 190, 150), (176, 138, 98)).convert('RGBA')
draw_layer, w, h = new_canvas(W, H)
d = ImageDraw.Draw(draw_layer)
egg_positions = [(0.35, 0.55), (0.55, 0.45), (0.68, 0.62), (0.42, 0.72)]
for (px, py) in egg_positions:
    cx, cy = int(px * W * SS), int(py * H * SS)
    rw, rh = int(58*SS), int(76*SS)
    d.ellipse([cx-rw, cy-rh, cx+rw, cy+rh], fill=(250, 244, 230, 255))
    d.ellipse([cx-rw*0.9, cy-rh*0.9, cx+rw*0.9, cy+rh*0.9], outline=(225, 210, 180, 255), width=int(2*SS))
    # opaque shine (fully opaque so it doesn't pick up the page background color)
    d.ellipse([cx-rw*0.4, cy-rh*0.55, cx-rw*0.05, cy-rh*0.1], fill=(255, 255, 255, 255))
draw_layer = draw_layer.filter(ImageFilter.GaussianBlur(1))
img = soft_shadow(draw_layer, blur=20, offset=(0, 18*SS), opacity=60)
img = finish(img, W, H)
final = Image.alpha_composite(bg, img).convert('RGB')
final.save(f"{OUT}/uploads/products/sample-eggs.jpg", quality=90)

# -------------------------------------------------------------
# 4. RAW WILDFLOWER HONEY
# -------------------------------------------------------------
bg = vertical_gradient((W, H), (255, 240, 200), (250, 200, 110)).convert('RGBA')
draw_layer, w, h = new_canvas(W, H)
d = ImageDraw.Draw(draw_layer)
cx, cy = int(0.5*W*SS), int(0.58*H*SS)
jar_w, jar_h = int(110*SS), int(150*SS)
# jar body (rounded rect)
d.rounded_rectangle([cx-jar_w, cy-jar_h, cx+jar_w, cy+jar_h], radius=int(20*SS), fill=(224, 149, 33, 255))
# honey shine (opaque lighter tint)
d.rounded_rectangle([cx-jar_w*0.6, cy-jar_h*0.7, cx-jar_w*0.25, cy+jar_h*0.6], radius=int(14*SS), fill=(240, 185, 100, 255))
# lid
lid_w, lid_h = int(jar_w*0.85), int(28*SS)
d.rounded_rectangle([cx-lid_w, cy-jar_h-lid_h, cx+lid_w, cy-jar_h+int(10*SS)], radius=int(8*SS), fill=(90, 60, 35, 255))
# label
label_h = int(60*SS)
d.rectangle([cx-jar_w*0.85, cy-label_h/2, cx+jar_w*0.85, cy+label_h/2], fill=(255, 250, 240, 230))
# little bee dots decoration
for bx, by in [(0.25, 0.25), (0.75, 0.2), (0.15, 0.75), (0.82, 0.7)]:
    ex, ey = int(bx*W*SS), int(by*H*SS)
    r = int(10*SS)
    d.ellipse([ex-r, ey-r, ex+r, ey+r], fill=(90, 60, 20, 200))
draw_layer = draw_layer.filter(ImageFilter.GaussianBlur(1))
img = soft_shadow(draw_layer, blur=20, offset=(0, 18*SS), opacity=55)
img = finish(img, W, H)
final = Image.alpha_composite(bg, img).convert('RGB')
final.save(f"{OUT}/uploads/products/sample-honey.jpg", quality=90)

# -------------------------------------------------------------
# 5. FRESH BASIL (herbs)
# -------------------------------------------------------------
bg = vertical_gradient((W, H), (223, 240, 205), (170, 209, 140)).convert('RGBA')
draw_layer, w, h = new_canvas(W, H)
d = ImageDraw.Draw(draw_layer)
random.seed(7)
cx, cy = int(0.5*W*SS), int(0.85*H*SS)

def draw_leaf(draw, tip, base, width):
    """Draw a pointed leaf (almond/pointed-oval) between base and tip using a polygon."""
    dx, dy = tip[0]-base[0], tip[1]-base[1]
    length = math.hypot(dx, dy)
    if length == 0:
        return
    ux, uy = dx/length, dy/length          # unit vector along the leaf
    px, py = -uy, ux                        # perpendicular unit vector
    mid = (base[0] + dx*0.45, base[1] + dy*0.45)
    pts = []
    steps = 10
    for i in range(steps+1):
        t = i/steps
        # width profile: 0 at base, max near middle, tapering to a point at tip
        wprof = math.sin(t * math.pi) ** 0.7
        cx_ = base[0] + dx*t
        cy_ = base[1] + dy*t
        pts.append((cx_ + px*width*wprof, cy_ + py*width*wprof))
    for i in range(steps, -1, -1):
        t = i/steps
        wprof = math.sin(t * math.pi) ** 0.7
        cx_ = base[0] + dx*t
        cy_ = base[1] + dy*t
        pts.append((cx_ - px*width*wprof, cy_ - py*width*wprof))
    draw.polygon(pts, fill=(90, 150, 74, 255))
    # center vein
    draw.line([base, tip], fill=(70, 120, 58, 180), width=max(1, int(width*0.12)))

for i in range(11):
    ang = random.uniform(-65, 65)
    length = random.uniform(0.32, 0.58) * H * SS
    lx = cx + math.sin(math.radians(ang)) * length
    ly = cy - math.cos(math.radians(ang)) * length
    # stem
    d.line([(cx, cy), (lx, ly)], fill=(74, 124, 62, 255), width=int(3.5*SS))
    draw_leaf(d, (lx, ly), (cx + math.sin(math.radians(ang))*length*0.55, cy - math.cos(math.radians(ang))*length*0.55), width=16*SS)
draw_layer = draw_layer.filter(ImageFilter.GaussianBlur(1))
img = soft_shadow(draw_layer, blur=20, offset=(0, 16*SS), opacity=45)
img = finish(img, W, H)
final = Image.alpha_composite(bg, img).convert('RGB')
final.save(f"{OUT}/uploads/products/sample-basil.jpg", quality=90)

# -------------------------------------------------------------
# 6. GENERIC DEFAULT (farm basket icon)
# -------------------------------------------------------------
bg = vertical_gradient((W, H), (232, 224, 200), (196, 178, 140)).convert('RGBA')
draw_layer, w, h = new_canvas(W, H)
d = ImageDraw.Draw(draw_layer)
cx, cy = int(0.5*W*SS), int(0.62*H*SS)
basket_w, basket_h = int(140*SS), int(90*SS)
d.polygon([(cx-basket_w, cy-basket_h*0.3), (cx+basket_w, cy-basket_h*0.3),
           (cx+basket_w*0.8, cy+basket_h), (cx-basket_w*0.8, cy+basket_h)], fill=(150, 100, 55, 255))
for i in range(-3, 4):
    x = cx + i * (basket_w // 3.2)
    d.line([(x, cy-basket_h*0.3), (x*0.9+cx*0.1, cy+basket_h)], fill=(120, 78, 40, 255), width=int(4*SS))
d.arc([cx-basket_w*0.6, cy-basket_h*1.3, cx+basket_w*0.6, cy-basket_h*0.1], start=200, end=340, fill=(120, 78, 40, 255), width=int(8*SS))
# produce peeking out
for (ox, oy, r, col) in [(-0.35, -0.42, 34, (196,47,39)), (0, -0.5, 30, (233,90,60)), (0.35, -0.4, 32, (94,148,78))]:
    ex, ey = cx + int(ox*basket_w), cy + int(oy*basket_h)
    rr = int(r*SS)
    d.ellipse([ex-rr, ey-rr, ex+rr, ey+rr], fill=(*col, 255))
draw_layer = draw_layer.filter(ImageFilter.GaussianBlur(1))
img = soft_shadow(draw_layer, blur=20, offset=(0, 16*SS), opacity=45)
img = finish(img, W, H)
final = Image.alpha_composite(bg, img).convert('RGB')
final.save(f"{OUT}/uploads/products/default-product.jpg", quality=90)
final.save(f"{OUT}/assets/images/default-product.jpg", quality=90)

print("Product images generated.")
