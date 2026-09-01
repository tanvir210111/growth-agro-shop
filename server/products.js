/**
 * Authoritative Server-Side Product & Pricing Configuration
 * 
 * Supports both E-Commerce Baby Fashion Products and Landing Page Agro Products.
 */

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
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 }
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
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 }
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
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 }
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
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 }
    }
  },
  // Landing Page Products
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
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 0 }
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
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 0 }
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
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 }
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
      "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 }
    }
  }
};

/**
 * Resolve product, variant, quantity and calculate authoritative totals
 */
function calculateOrderTotals(productId, variantId, quantity, deliveryZone, items) {
  let product = PRODUCTS[productId];
  
  if (!product) {
    // Dynamic fallback for any baby product ID
    product = {
      id: productId,
      name: `Baby Outfit (${productId})`,
      shortName: productId,
      sku: `BABY-${productId.slice(0,6).toUpperCase()}`,
      currency: "BDT",
      currencySymbol: "৳",
      source: "Baby Store",
      variants: {
        [variantId]: { id: variantId, name: variantId || "Standard", price: 250, regularPrice: 350, freeDelivery: false }
      },
      deliveryZones: {
        "inside": { id: "inside", label: "ঢাকার ভিতরে", charge: 60 },
        "outside": { id: "outside", label: "ঢাকার বাইরে", charge: 120 }
      }
    };
  }

  // If multi-item array is passed
  if (Array.isArray(items) && items.length > 0) {
    let subtotal = 0;
    let totalQty = 0;
    const summaries = [];
    let isFreeDelivery = true;

    for (const it of items) {
      const itQty = parseInt(it.quantity || 0, 10);
      if (itQty > 0) {
        const itPrice = parseFloat(it.price) || 0;
        const itVariant = (product.variants && product.variants[it.variantId]) || {
          price: itPrice > 0 ? itPrice : 2300,
          name: it.name || it.variantId,
          freeDelivery: itPrice > 0 ? false : true
        };
        const unitP = itVariant.price || itPrice;
        subtotal += unitP * itQty;
        totalQty += itQty;
        summaries.push(`${itVariant.name || it.name || it.variantId} × ${itQty}`);
        if (!itVariant.freeDelivery) isFreeDelivery = false;
      }
    }

    if (totalQty > 0) {
      const deliveryOption = (product.deliveryZones && product.deliveryZones[deliveryZone]) 
        ? product.deliveryZones[deliveryZone] 
        : { id: deliveryZone || "inside", label: deliveryZone === 'outside' ? "ঢাকার বাইরে" : "ঢাকার ভিতরে", charge: deliveryZone === 'outside' ? 120 : 80 };
      const deliveryCharge = isFreeDelivery ? 0 : deliveryOption.charge;
      const total = subtotal + deliveryCharge;

      return {
        product,
        variant: {
          id: items.map(i => i.variantId).join('+'),
          name: summaries.join(' + '),
          price: totalQty > 0 ? Math.round(subtotal / totalQty) : 0,
          freeDelivery: isFreeDelivery
        },
        quantity: totalQty,
        deliveryZone: deliveryOption.id,
        deliveryZoneLabel: deliveryOption.label,
        unitPrice: totalQty > 0 ? Math.round(subtotal / totalQty) : 0,
        subtotal,
        deliveryCharge,
        total,
        currency: product.currency || "BDT"
      };
    }
  }

  // Resolve single variant
  let variant = product.variants ? product.variants[variantId] : null;
  if (!variant && product.variants) {
    const variantKeys = Object.keys(product.variants);
    variant = product.variants[variantKeys[0]];
  }
  if (!variant) {
    variant = { id: variantId || "default", name: "Standard", price: 250, regularPrice: 350, freeDelivery: false };
  }

  const qty = parseInt(quantity, 10);
  const safeQty = (isNaN(qty) || qty < 1 || qty > 50) ? 1 : qty;

  const deliveryOption = (product.deliveryZones && product.deliveryZones[deliveryZone]) 
    ? product.deliveryZones[deliveryZone] 
    : { id: "outside", label: "ঢাকার বাইরে", charge: 120 };

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
    currency: product.currency || "BDT"
  };
}

module.exports = {
  PRODUCTS,
  calculateOrderTotals
};
