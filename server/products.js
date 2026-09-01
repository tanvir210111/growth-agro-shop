/**
 * Authoritative Server-Side Product & Pricing Configuration
 * 
 * Supports:
 * 1. Dynamic Universal Landing Pages from Laravel database (landing_pages table)
 * 2. Legacy Storefront & Baby Fashion Products (PRODUCTS catalogue)
 * 3. Classic Poultry Agro Products (Chicken Booster, Broiler Booster, Layer Booster)
 */

const { DatabaseSync } = require('node:sqlite');
const path = require('path');
const fs = require('fs');

let _cachedDb = null;
let _cachedDbPath = null;

/**
 * Open connection to the authoritative Laravel SQLite database
 */
function getLaravelDb() {
  const possiblePaths = [
    process.env.DB_DATABASE,
    path.join(__dirname, '..', 'E Commerce Baby', 'database', 'database.sqlite'),
    path.join(__dirname, 'E Commerce Baby', 'database', 'database.sqlite'),
    path.join(process.cwd(), 'E Commerce Baby', 'database', 'database.sqlite'),
    path.join(process.cwd(), 'database', 'database.sqlite')
  ].filter(Boolean);

  for (const p of possiblePaths) {
    if (fs.existsSync(p)) {
      if (_cachedDb && _cachedDbPath === p) {
        return _cachedDb;
      }
      try {
        _cachedDb = new DatabaseSync(p, { readOnly: true });
        _cachedDbPath = p;
        return _cachedDb;
      } catch (err) {
        try {
          _cachedDb = new DatabaseSync(p);
          _cachedDbPath = p;
          return _cachedDb;
        } catch (e) {}
      }
    }
  }
  return null;
}

/**
 * Resolve a landing page by slug from the Laravel landing_pages database
 */
function findLandingPageInDb(slug) {
  if (!slug) return null;
  const cleanSlug = String(slug).trim()
    .replace(/^\/product\//, '')
    .replace(/^\/products\//, '')
    .replace(/\/$/, '')
    .toLowerCase();

  const db = getLaravelDb();
  if (!db) return null;

  try {
    const stmt = db.prepare('SELECT id, name, slug, status, product_id, product_name, content, delivery_config, theme_config FROM landing_pages WHERE LOWER(slug) = ? LIMIT 1');
    const row = stmt.get(cleanSlug);
    if (!row) return null;

    let content = {};
    let delivery_config = {};
    try {
      content = typeof row.content === 'string' ? JSON.parse(row.content || '{}') : (row.content || {});
    } catch (e) {}
    try {
      delivery_config = typeof row.delivery_config === 'string' ? JSON.parse(row.delivery_config || '{}') : (row.delivery_config || {});
    } catch (e) {}

    return {
      id: row.id,
      name: row.name,
      slug: row.slug,
      status: row.status,
      product_id: row.product_id,
      product_name: row.product_name,
      content,
      delivery_config
    };
  } catch (err) {
    return null;
  }
}

const PRODUCTS = {
  // Baby Products Catalogue
  "baby-butterfly-set": {
    id: "baby-butterfly-set",
    name: "Girls Red Butterfly Printed T-Shirt & Floral Shorts 2-Piece Set",
    shortName: "Butterfly 2-Piece Set",
    sku: "BABY-0152",
    currency: "BDT",
    currencySymbol: "৳",
    source: "Baby Store",
    variants: {
      "6-12M": { id: "6-12M", name: "6-12 Months", price: 200, regularPrice: 299, freeDelivery: false },
      "1-2Y": { id: "1-2Y", name: "1-2 Years", price: 200, regularPrice: 299, freeDelivery: false },
      "2-3Y": { id: "2-3Y", name: "2-3 Years", price: 200, regularPrice: 299, freeDelivery: false },
      "3-4Y": { id: "3-4Y", name: "3-4 Years", price: 200, regularPrice: 299, freeDelivery: false },
      "default": { id: "default", name: "Standard Size", price: 200, regularPrice: 299, freeDelivery: false }
    },
    deliveryZones: {
      "inside": { id: "inside", label: "ঢাকার ভিতরে", charge: 60 },
      "inside_dhaka": { id: "inside_dhaka", label: "ঢাকার ভিতরে", charge: 60 },
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 },
      "outside_dhaka": { id: "outside_dhaka", label: "ঢাকার বাইরে", charge: 120 }
    }
  },
  "baby-frozen-elsa": {
    id: "baby-frozen-elsa",
    name: "Girls Disney Frozen Elsa Ruffle Top & Shorts Set",
    shortName: "Frozen Elsa Ruffle Set",
    sku: "BABY-0192",
    currency: "BDT",
    currencySymbol: "৳",
    source: "Baby Store",
    variants: {
      "6-12M": { id: "6-12M", name: "6-12 Months", price: 250, regularPrice: 299, freeDelivery: false },
      "1-2Y": { id: "1-2Y", name: "1-2 Years", price: 250, regularPrice: 299, freeDelivery: false },
      "2-3Y": { id: "2-3Y", name: "2-3 Years", price: 250, regularPrice: 299, freeDelivery: false },
      "default": { id: "default", name: "Standard Size", price: 250, regularPrice: 299, freeDelivery: false }
    },
    deliveryZones: {
      "inside": { id: "inside", label: "ঢাকার ভিতরে", charge: 60 },
      "inside_dhaka": { id: "inside_dhaka", label: "ঢাকার ভিতরে", charge: 60 },
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 },
      "outside_dhaka": { id: "outside_dhaka", label: "ঢাকার বাইরে", charge: 120 }
    }
  },
  "baby-premium-blazer-brown": {
    id: "baby-premium-blazer-brown",
    name: "Girls Premium Blazer and Wide Leg Pants Set (Brown)",
    shortName: "Girls Premium Blazer Set",
    sku: "BABY-0169",
    currency: "BDT",
    currencySymbol: "৳",
    source: "Baby Store",
    variants: {
      "1-2Y": { id: "1-2Y", name: "1-2 Years", price: 899, regularPrice: 1250, freeDelivery: false },
      "2-3Y": { id: "2-3Y", name: "2-3 Years", price: 899, regularPrice: 1250, freeDelivery: false },
      "3-4Y": { id: "3-4Y", name: "3-4 Years", price: 899, regularPrice: 1250, freeDelivery: false },
      "default": { id: "default", name: "Standard Size", price: 899, regularPrice: 1250, freeDelivery: false }
    },
    deliveryZones: {
      "inside": { id: "inside", label: "ঢাকার ভিতরে", charge: 60 },
      "inside_dhaka": { id: "inside_dhaka", label: "ঢাকার ভিতরে", charge: 60 },
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 },
      "outside_dhaka": { id: "outside_dhaka", label: "ঢাকার বাইরে", charge: 120 }
    }
  },
  "baby-bow-set-black": {
    id: "baby-bow-set-black",
    name: "Girls Black Color Bow Printed T-Shirt & Shorts Set",
    shortName: "Black Bow Printed Set",
    sku: "BABY-0190",
    currency: "BDT",
    currencySymbol: "৳",
    source: "Baby Store",
    variants: {
      "6-12M": { id: "6-12M", name: "6-12 Months", price: 250, regularPrice: 299, freeDelivery: false },
      "1-2Y": { id: "1-2Y", name: "1-2 Years", price: 250, regularPrice: 299, freeDelivery: false },
      "default": { id: "default", name: "Standard Size", price: 250, regularPrice: 299, freeDelivery: false }
    },
    deliveryZones: {
      "inside": { id: "inside", label: "ঢাকার ভিতরে", charge: 60 },
      "inside_dhaka": { id: "inside_dhaka", label: "ঢাকার ভিতরে", charge: 60 },
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 },
      "outside_dhaka": { id: "outside_dhaka", label: "ঢাকার বাইরে", charge: 120 }
    }
  },
  // Landing Page Products Catalogue (Preserved for legacy / offline compatibility)
  "chicken-booster": {
    id: "chicken-booster",
    name: "চিকেন বুস্টার — পোল্ট্রি গ্রোথ ও রোগ প্রতিরোধক ফর্মুলা",
    shortName: "চিকেন বুস্টার (Chicken Booster)",
    sku: "CB-AGRO-01",
    currency: "BDT",
    currencySymbol: "৳",
    source: "Landing Page",
    landingPage: "/products/chicken-booster/",
    variants: {
      "variant-1": { id: "variant-1", name: "১ টি প্যাক (৫০০ গ্রাম)", quantityPerUnit: 1, weight: "500g", price: 990, regularPrice: 1350, freeDelivery: false },
      "1-pack": { id: "1-pack", name: "১ টি প্যাক (৫০০ গ্রাম)", quantityPerUnit: 1, weight: "500g", price: 1050, regularPrice: 1250, freeDelivery: false },
      "variant-2": { id: "variant-2", name: "২ টি প্যাক (১ কেজি কম্বো)", quantityPerUnit: 2, weight: "1kg", price: 1850, regularPrice: 2700, freeDelivery: false },
      "2-pack": { id: "2-pack", name: "২ টি প্যাক (১ কেজি কম্বো)", quantityPerUnit: 2, weight: "1kg", price: 1850, regularPrice: 2400, freeDelivery: false },
      "variant-3": { id: "variant-3", name: "৪ টি প্যাক (২ কেজি মেগা সেভার)", quantityPerUnit: 4, weight: "2kg", price: 3400, regularPrice: 5400, freeDelivery: true },
      "4-pack": { id: "4-pack", name: "৪ টি প্যাক (২ কেজি মেগা সেভার)", quantityPerUnit: 4, weight: "2kg", price: 3400, regularPrice: 4600, freeDelivery: true },
      "broiler-1kg": { id: "broiler-1kg", name: "Broiler Booster (১ কেজি)", quantityPerUnit: 1, weight: "1kg", price: 2300, regularPrice: 2800, freeDelivery: true },
      "layer-1kg": { id: "layer-1kg", name: "Layer Booster (১ কেজি)", quantityPerUnit: 1, weight: "1kg", price: 2300, regularPrice: 2800, freeDelivery: true },
      "combo-2kg": { id: "combo-2kg", name: "সুপার কম্বো অফার (Broiler 1KG + Layer 1KG)", quantityPerUnit: 2, weight: "2kg", price: 2300, regularPrice: 3100, freeDelivery: true }
    },
    deliveryZones: {
      "inside": { id: "inside", label: "ঢাকার ভিতরে", charge: 0 },
      "inside_dhaka": { id: "inside_dhaka", label: "ঢাকার ভিতরে", charge: 0 },
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 0 },
      "outside_dhaka": { id: "outside_dhaka", label: "ঢাকার বাইরে", charge: 0 }
    }
  },
  "broiler-booster": {
    id: "broiler-booster",
    name: "Broiler Booster (ব্রয়লার বুস্টার)",
    shortName: "Broiler Booster",
    sku: "BGM-01",
    currency: "BDT",
    currencySymbol: "৳",
    source: "Landing Page",
    variants: {
      "default": { id: "default", name: "১ কেজি প্যাক", price: 2300, regularPrice: 2800, freeDelivery: true },
      "1kg": { id: "1kg", name: "১ কেজি প্যাক", price: 2300, regularPrice: 2800, freeDelivery: true },
      "broiler-1kg": { id: "broiler-1kg", name: "Broiler Booster (১ কেজি)", price: 2300, regularPrice: 2800, freeDelivery: true }
    },
    deliveryZones: {
      "inside": { id: "inside", label: "ঢাকার ভিতরে", charge: 0 },
      "inside_dhaka": { id: "inside_dhaka", label: "ঢাকার ভিতরে", charge: 0 },
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 0 },
      "outside_dhaka": { id: "outside_dhaka", label: "ঢাকার বাইরে", charge: 0 }
    }
  },
  "layer-booster": {
    id: "layer-booster",
    name: "Layer Booster (লেয়ার বুস্টার)",
    shortName: "Layer Booster",
    sku: "LB-01",
    currency: "BDT",
    currencySymbol: "৳",
    source: "Landing Page",
    variants: {
      "default": { id: "default", name: "১ কেজি প্যাক", price: 2300, regularPrice: 2800, freeDelivery: true },
      "1kg": { id: "1kg", name: "১ কেজি প্যাক", price: 2300, regularPrice: 2800, freeDelivery: true },
      "layer-1kg": { id: "layer-1kg", name: "Layer Booster (১ কেজি)", price: 2300, regularPrice: 2800, freeDelivery: true }
    },
    deliveryZones: {
      "inside": { id: "inside", label: "ঢাকার ভিতরে", charge: 0 },
      "inside_dhaka": { id: "inside_dhaka", label: "ঢাকার ভিতরে", charge: 0 },
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 0 },
      "outside_dhaka": { id: "outside_dhaka", label: "ঢাকার বাইরে", charge: 0 }
    }
  },
  "chicken-booster-1": {
    id: "chicken-booster-1",
    name: "চিকেন বুস্টার — ১ প্যাক (৫০০ গ্রাম)",
    shortName: "চিকেন বুস্টার ১ প্যাক",
    sku: "CB-001",
    currency: "BDT",
    currencySymbol: "৳",
    source: "Storefront",
    variants: {
      "default": { id: "default", name: "১ প্যাক (৫০০ গ্রাম)", price: 1050, regularPrice: 1250, freeDelivery: false }
    },
    deliveryZones: {
      "inside": { id: "inside", label: "ঢাকার ভিতরে", charge: 60 },
      "inside_dhaka": { id: "inside_dhaka", label: "ঢাকার ভিতরে", charge: 60 },
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 },
      "outside_dhaka": { id: "outside_dhaka", label: "ঢাকার বাইরে", charge: 120 }
    }
  },
  "chicken-booster-2": {
    id: "chicken-booster-2",
    name: "চিকেন বুস্টার — ২ প্যাক কম্বো (১ কেজি)",
    shortName: "চিকেন বুস্টার ২ প্যাক",
    sku: "CB-002",
    currency: "BDT",
    currencySymbol: "৳",
    source: "Storefront",
    variants: {
      "default": { id: "default", name: "২ প্যাক (১ কেজি)", price: 1850, regularPrice: 2400, freeDelivery: false },
      "2-pack": { id: "2-pack", name: "২ প্যাক (১ কেজি)", price: 1850, regularPrice: 2400, freeDelivery: false }
    },
    deliveryZones: {
      "inside": { id: "inside", label: "ঢাকার ভিতরে", charge: 60 },
      "inside_dhaka": { id: "inside_dhaka", label: "ঢাকার ভিতরে", charge: 60 },
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 },
      "outside_dhaka": { id: "outside_dhaka", label: "ঢাকার বাইরে", charge: 120 }
    }
  }
};

/**
 * Resolve product, variant, quantity and calculate authoritative server-side totals.
 *
 * 1. For dynamic Universal Landing Pages (/product/{slug}):
 *    - Resolves package price, regular price, and delivery rules directly from the Laravel landing_pages database.
 *    - Ignores all client-supplied prices.
 *    - Rejects invalid packages/slugs with clean 400 Bad Request error.
 *
 * 2. For legacy storefront / hardcoded products:
 *    - Resolves from PRODUCTS catalogue with authoritative variant pricing.
 */
function calculateOrderTotals(productId, variantId, quantity, deliveryZone, items, options = {}) {
  const targetSlug = String(options.slug || options.landingPage || options.landing_page || productId || '').trim();
  const isInside = (deliveryZone === 'inside' || deliveryZone === 'inside_dhaka');

  // Step 1: Attempt to resolve from the authoritative Laravel landing_pages Database
  const lp = findLandingPageInDb(targetSlug) || (targetSlug !== productId ? findLandingPageInDb(productId) : null);

  if (lp) {
    const rawPackages = Array.isArray(lp.content?.packages) ? lp.content.packages : [];
    const packageMap = {};

    for (const pkg of rawPackages) {
      const key = String(pkg.id || '').trim();
      if (key) {
        const pObj = {
          id: key,
          name: pkg.name || `${lp.product_name || lp.name} (${key})`,
          price: typeof pkg.price === 'number' ? pkg.price : (parseFloat(pkg.price) || 0),
          regularPrice: typeof pkg.old_price === 'number' ? pkg.old_price : (parseFloat(pkg.old_price) || (typeof pkg.price === 'number' ? pkg.price : parseFloat(pkg.price) || 0)),
          weight: pkg.weight || '',
          image: pkg.image || '',
          is_active: pkg.is_active !== false
        };
        packageMap[key] = pObj;
        packageMap[key.toLowerCase()] = pObj;
      }
    }

    // Merge legacy aliases if this slug exists in PRODUCTS (e.g. chicken-booster legacy variant aliases)
    if (PRODUCTS[lp.slug] && PRODUCTS[lp.slug].variants) {
      for (const [vKey, vObj] of Object.entries(PRODUCTS[lp.slug].variants)) {
        if (!packageMap[vKey]) {
          const lObj = {
            id: vKey,
            name: vObj.name,
            price: vObj.price,
            regularPrice: vObj.regularPrice || vObj.price,
            weight: vObj.weight || '',
            image: ''
          };
          packageMap[vKey] = lObj;
          packageMap[vKey.toLowerCase()] = lObj;
        }
      }
    }

    // A. Multi-item landing page order
    if (Array.isArray(items) && items.length > 0) {
      let subtotal = 0;
      let totalQty = 0;
      const summaries = [];
      const calculatedItems = [];

      for (const it of items) {
        const itQty = parseInt(it.quantity || 0, 10);
        if (itQty > 0) {
          const itVariantKey = String(it.variantId || it.variant_id || it.id || it.package_id || '').trim();
          const authPkg = packageMap[itVariantKey] || packageMap[itVariantKey.toLowerCase()];

          if (!authPkg) {
            throw new Error(`প্যাকেজ পাওয়া যায়নি: "${itVariantKey}" (Landing Page: ${lp.slug})`);
          }

          // Authoritative calculation - NEVER trust client it.price
          const unitP = authPkg.price;
          const itTotal = unitP * itQty;
          subtotal += itTotal;
          totalQty += itQty;
          summaries.push(`${authPkg.name} × ${itQty}`);
          calculatedItems.push({
            id: authPkg.id,
            variant_id: authPkg.id,
            name: authPkg.name,
            variant_name: authPkg.name,
            price: unitP,
            regularPrice: authPkg.regularPrice,
            quantity: itQty,
            total: itTotal,
            image: authPkg.image
          });
        }
      }

      if (totalQty === 0) {
        throw new Error('অর্ডারের পরিমাণ কমপক্ষে ১ হতে হবে');
      }

      // Authoritative Delivery calculation from landing page config
      const dConfig = lp.delivery_config || {};
      const dType = dConfig.delivery_type || 'free';
      const isSame = !!dConfig.same_charge_everywhere;
      const isFreeAbove = !!dConfig.free_delivery_above;
      const freeThreshold = parseFloat(dConfig.free_delivery_threshold) || 1000;
      const chargeInside = parseFloat(dConfig.charge_inside_dhaka) || 0;
      const chargeOutside = parseFloat(dConfig.charge_outside_dhaka) || 0;

      let deliveryCharge = 0;
      if (dType !== 'free') {
        if (isSame) {
          deliveryCharge = chargeInside;
        } else if (isInside) {
          deliveryCharge = chargeInside;
        } else {
          deliveryCharge = chargeOutside;
        }

        if (isFreeAbove && subtotal >= freeThreshold) {
          deliveryCharge = 0;
        }
      }

      const total = subtotal + deliveryCharge;
      const avgUnitPrice = totalQty > 0 ? Math.round(subtotal / totalQty) : 0;

      return {
        product: {
          id: lp.slug,
          name: lp.product_name || lp.name,
          shortName: lp.product_name || lp.name,
          sku: lp.product_id || `LP-${lp.slug.slice(0, 8).toUpperCase()}`,
          currency: "BDT",
          currencySymbol: "৳",
          source: "Landing Page",
          landingPage: `/product/${lp.slug}`
        },
        variant: {
          id: calculatedItems.map(i => i.id).join('+'),
          name: summaries.join(' + '),
          price: avgUnitPrice,
          regularPrice: calculatedItems[0]?.regularPrice || avgUnitPrice
        },
        quantity: totalQty,
        deliveryZone: isInside ? "inside" : "outside",
        deliveryZoneLabel: isInside ? (dConfig.inside_label || "ঢাকার ভিতরে") : (dConfig.outside_label || "ঢাকার বাইরে"),
        unitPrice: avgUnitPrice,
        subtotal,
        deliveryCharge,
        total,
        currency: "BDT",
        items: calculatedItems
      };
    }

    // B. Single-item landing page order
    const vKey = String(variantId || '').trim();
    let authPkg = packageMap[vKey] || packageMap[vKey.toLowerCase()];
    if (!authPkg && (!vKey || vKey === 'default') && Object.keys(packageMap).length > 0) {
      authPkg = Object.values(packageMap)[0];
    }

    if (!authPkg) {
      throw new Error(`প্যাকেজ পাওয়া যায়নি: "${variantId}" (Landing Page: ${lp.slug})`);
    }

    const safeQty = (isNaN(quantity) || quantity < 1 || quantity > 50) ? 1 : parseInt(quantity, 10);
    const subtotal = authPkg.price * safeQty;
    const totalQty = safeQty;

    // Authoritative Delivery calculation from landing page config
    const dConfig = lp.delivery_config || {};
    const dType = dConfig.delivery_type || 'free';
    const isSame = !!dConfig.same_charge_everywhere;
    const isFreeAbove = !!dConfig.free_delivery_above;
    const freeThreshold = parseFloat(dConfig.free_delivery_threshold) || 1000;
    const chargeInside = parseFloat(dConfig.charge_inside_dhaka) || 0;
    const chargeOutside = parseFloat(dConfig.charge_outside_dhaka) || 0;

    let deliveryCharge = 0;
    if (dType !== 'free') {
      if (isSame) {
        deliveryCharge = chargeInside;
      } else if (isInside) {
        deliveryCharge = chargeInside;
      } else {
        deliveryCharge = chargeOutside;
      }

      if (isFreeAbove && subtotal >= freeThreshold) {
        deliveryCharge = 0;
      }
    }

    const total = subtotal + deliveryCharge;

    return {
      product: {
        id: lp.slug,
        name: lp.product_name || lp.name,
        shortName: lp.product_name || lp.name,
        sku: lp.product_id || `LP-${lp.slug.slice(0, 8).toUpperCase()}`,
        currency: "BDT",
        currencySymbol: "৳",
        source: "Landing Page",
        landingPage: `/product/${lp.slug}`
      },
      variant: {
        id: authPkg.id,
        name: authPkg.name,
        price: authPkg.price,
        regularPrice: authPkg.regularPrice
      },
      quantity: totalQty,
      deliveryZone: isInside ? "inside" : "outside",
      deliveryZoneLabel: isInside ? (dConfig.inside_label || "ঢাকার ভিতরে") : (dConfig.outside_label || "ঢাকার বাইরে"),
      unitPrice: authPkg.price,
      subtotal,
      deliveryCharge,
      total,
      currency: "BDT",
      items: [{
        id: authPkg.id,
        variant_id: authPkg.id,
        name: authPkg.name,
        variant_name: authPkg.name,
        price: authPkg.price,
        regularPrice: authPkg.regularPrice,
        quantity: totalQty,
        total: subtotal,
        image: authPkg.image
      }]
    };
  }

  // Step 2: Fallback to hardcoded PRODUCTS catalogue (Legacy storefront products)
  const product = PRODUCTS[productId] || PRODUCTS[targetSlug];

  if (!product) {
    if (options.source === 'LANDING_PAGE' || targetSlug.startsWith('landing-') || options.isLandingPage) {
      throw new Error('এই পণ্যের মূল্য বা প্যাকেজ তথ্য পাওয়া যায়নি।');
    }
    throw new Error(`পণ্য পাওয়া যায়নি: "${productId}"`);
  }

  // Multi-item order for legacy product
  if (Array.isArray(items) && items.length > 0) {
    let subtotal = 0;
    let totalQty = 0;
    const summaries = [];
    let isFreeDelivery = true;
    const calculatedItems = [];

    for (const it of items) {
      const itQty = parseInt(it.quantity || 0, 10);
      if (itQty > 0) {
        const itVariant = (product.variants && (product.variants[it.variantId] || product.variants[it.variant_id])) || {
          price: product.variants?.default?.price || 200,
          name: it.name || it.variantId,
          freeDelivery: false
        };
        const unitP = itVariant.price;
        const itTotal = unitP * itQty;
        subtotal += itTotal;
        totalQty += itQty;
        summaries.push(`${itVariant.name || it.name || it.variantId} × ${itQty}`);
        if (!itVariant.freeDelivery) isFreeDelivery = false;
        calculatedItems.push({
          id: itVariant.id || it.variantId,
          variant_id: itVariant.id || it.variantId,
          name: itVariant.name || it.name,
          price: unitP,
          regularPrice: itVariant.regularPrice || unitP,
          quantity: itQty,
          total: itTotal
        });
      }
    }

    if (totalQty === 0) {
      throw new Error('অর্ডারের পরিমাণ কমপক্ষে ১ হতে হবে');
    }

    const deliveryOption = (product.deliveryZones && (product.deliveryZones[deliveryZone] || (isInside ? product.deliveryZones.inside : product.deliveryZones.outside)))
      ? (product.deliveryZones[deliveryZone] || (isInside ? product.deliveryZones.inside : product.deliveryZones.outside))
      : { id: isInside ? "inside" : "outside", label: isInside ? "ঢাকার ভিতরে" : "ঢাকার বাইরে", charge: isInside ? 60 : 120 };
    const deliveryCharge = isFreeDelivery ? 0 : deliveryOption.charge;
    const total = subtotal + deliveryCharge;
    const avgUnitPrice = totalQty > 0 ? Math.round(subtotal / totalQty) : 0;

    return {
      product,
      variant: {
        id: calculatedItems.map(i => i.id).join('+'),
        name: summaries.join(' + '),
        price: avgUnitPrice,
        freeDelivery: isFreeDelivery
      },
      quantity: totalQty,
      deliveryZone: deliveryOption.id,
      deliveryZoneLabel: deliveryOption.label,
      unitPrice: avgUnitPrice,
      subtotal,
      deliveryCharge,
      total,
      currency: product.currency || "BDT",
      items: calculatedItems
    };
  }

  // Single variant for legacy product
  let variant = product.variants ? (product.variants[variantId] || product.variants[String(variantId).toLowerCase()]) : null;
  if (!variant && product.variants) {
    const variantKeys = Object.keys(product.variants);
    variant = product.variants[variantKeys[0]];
  }
  if (!variant) {
    throw new Error(`ভ্যারিয়েন্ট পাওয়া যায়নি: "${variantId}" (Product: ${product.name})`);
  }

  const qty = parseInt(quantity, 10);
  const safeQty = (isNaN(qty) || qty < 1 || qty > 50) ? 1 : qty;

  const deliveryOption = (product.deliveryZones && (product.deliveryZones[deliveryZone] || (isInside ? product.deliveryZones.inside : product.deliveryZones.outside)))
    ? (product.deliveryZones[deliveryZone] || (isInside ? product.deliveryZones.inside : product.deliveryZones.outside))
    : { id: isInside ? "inside" : "outside", label: isInside ? "ঢাকার ভিতরে" : "ঢাকার বাইরে", charge: isInside ? 60 : 120 };

  const unitPrice = variant.price;
  const subtotal = unitPrice * safeQty;
  const deliveryCharge = variant.freeDelivery ? 0 : deliveryOption.charge;
  const total = subtotal + deliveryCharge;

  return {
    product,
    variant,
    quantity: safeQty,
    deliveryZone: deliveryOption.id,
    deliveryZoneLabel: deliveryOption.label,
    unitPrice,
    subtotal,
    deliveryCharge,
    total,
    currency: product.currency || "BDT",
    items: [{
      id: variant.id,
      variant_id: variant.id,
      name: variant.name,
      price: unitPrice,
      regularPrice: variant.regularPrice || unitPrice,
      quantity: safeQty,
      total: subtotal
    }]
  };
}

module.exports = {
  PRODUCTS,
  findLandingPageInDb,
  calculateOrderTotals
};
