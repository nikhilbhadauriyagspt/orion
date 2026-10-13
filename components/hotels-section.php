<?php
/**
 * Hotels & Luxury Resorts Component - Orion Advent
 * - Location-Wise Exploration: Interactive Destination Switcher Cards at Top
 * - Lightweight Modern Studio/Suite Card UI (Horizontal Flex, Not Heavy)
 * - Two-Row Slider Layout: Both rows slide together horizontally (2 Full Cards + 1 Peeking Card)
 * - Continuous Marquee Slide for Amenities (Free Breakfast, Ocean Pool, Spa, Wi-Fi, etc.)
 * - 100% Flat, Border-First, Zero Shadows
 * - Pure Font Awesome 6 Icons Only (Zero Emojis)
 */

$hotelDestinations = [
    [
        'id' => 'all',
        'name' => 'All Locations',
        'count' => '24+ Stays',
        'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=400&q=80',
        'icon' => 'fa-solid fa-hotel',
    ],
    [
        'id' => 'goa',
        'name' => 'Goa',
        'count' => '4 Beachfront Stays',
        'image' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=400&q=80',
        'icon' => 'fa-solid fa-umbrella-beach',
    ],
    [
        'id' => 'manali',
        'name' => 'Manali',
        'count' => '4 Alpine Stays',
        'image' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=400&q=80',
        'icon' => 'fa-solid fa-mountain',
    ],
    [
        'id' => 'jaipur',
        'name' => 'Jaipur',
        'count' => '4 Royal Havelis',
        'image' => 'https://images.unsplash.com/photo-1599661046289-e31897846e41?auto=format&fit=crop&w=400&q=80',
        'icon' => 'fa-solid fa-chess-rook',
    ],
    [
        'id' => 'kerala',
        'name' => 'Kerala',
        'count' => '4 Lake Resorts',
        'image' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=400&q=80',
        'icon' => 'fa-solid fa-water',
    ],
    [
        'id' => 'dubai',
        'name' => 'Dubai',
        'count' => '4 5★ Stays',
        'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=400&q=80',
        'icon' => 'fa-solid fa-city',
    ],
    [
        'id' => 'udaipur',
        'name' => 'Udaipur',
        'count' => '4 Lake Palaces',
        'image' => 'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=400&q=80',
        'icon' => 'fa-solid fa-crown',
    ]
];

$hotelsList = [
    // 1. GOA HOTELS (4 STAYS)
    [
        'id' => 'HTL-GOA-01',
        'name' => 'Taj Fort Aguada Resort & Spa',
        'location_tag' => 'goa',
        'location_text' => 'Sinquerim Beach, North Goa',
        'star_badge' => '5★ Luxury',
        'star_class' => 'bg-amber-500/90 text-white border-amber-400',
        'rating' => '4.9',
        'reviews' => '420',
        'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Taj Fort Aguada Beach Resort',
        'amenities' => [
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Ocean Infinity Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Spa & Wellness', 'icon' => 'fa-solid fa-spa'],
            ['name' => 'Beachfront Access', 'icon' => 'fa-solid fa-umbrella-beach'],
            ['name' => 'Free High-Speed Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Pay at Hotel',
        'price' => 8499,
        'original_price' => 11000,
    ],
    [
        'id' => 'HTL-GOA-02',
        'name' => 'W Goa Beachfront Retreat',
        'location_tag' => 'goa',
        'location_text' => 'Vagator Beach, North Goa',
        'star_badge' => '5★ Lifestyle',
        'star_class' => 'bg-purple-600/90 text-white border-purple-400',
        'rating' => '4.9',
        'reviews' => '315',
        'image' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=800&q=80',
        'alt' => 'W Goa Beach Resort Pool',
        'amenities' => [
            ['name' => 'Sunset Pool Bar', 'icon' => 'fa-solid fa-martini-glass-citrus'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Rockpool Club', 'icon' => 'fa-solid fa-umbrella-beach'],
            ['name' => 'Spa & Sauna', 'icon' => 'fa-solid fa-spa'],
            ['name' => 'High-Speed Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation up to 24h',
        'price' => 11999,
        'original_price' => 15500,
    ],
    [
        'id' => 'HTL-GOA-03',
        'name' => 'Novotel Goa Candolim Resort',
        'location_tag' => 'goa',
        'location_text' => 'Candolim Beach Road, Goa',
        'star_badge' => '4★ Family Stay',
        'star_class' => 'bg-brand-600/90 text-white border-brand-400',
        'rating' => '4.8',
        'reviews' => '280',
        'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Novotel Goa Pool Candolim',
        'amenities' => [
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Tropical Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Kids Play Zone', 'icon' => 'fa-solid fa-shapes'],
            ['name' => 'Fitness Center', 'icon' => 'fa-solid fa-dumbbell'],
            ['name' => 'Free Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Instant Booking',
        'price' => 5499,
        'original_price' => 7200,
    ],
    [
        'id' => 'HTL-GOA-04',
        'name' => 'Alila Diwa South Goa Sanctuary',
        'location_tag' => 'goa',
        'location_text' => 'Majorda Beach, South Goa',
        'star_badge' => '5★ Eco Resort',
        'star_class' => 'bg-emerald-600/90 text-white border-emerald-400',
        'rating' => '4.9',
        'reviews' => '260',
        'image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Alila Diwa South Goa Infinity Pool',
        'amenities' => [
            ['name' => 'Infinity Paddy Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Ayurvedic Spa', 'icon' => 'fa-solid fa-spa'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Open-Air Dining', 'icon' => 'fa-solid fa-utensils'],
            ['name' => 'Free High-Speed Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Pay at Hotel',
        'price' => 9299,
        'original_price' => 12500,
    ],

    // 2. MANALI HOTELS (4 STAYS)
    [
        'id' => 'HTL-MNL-05',
        'name' => 'The Himalayan Castle & Resort',
        'location_tag' => 'manali',
        'location_text' => 'Hadimba Road, Old Manali',
        'star_badge' => '5★ Alpine Castle',
        'star_class' => 'bg-amber-500/90 text-white border-amber-400',
        'rating' => '4.9',
        'reviews' => '340',
        'image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80',
        'alt' => 'The Himalayan Castle Resort Manali',
        'amenities' => [
            ['name' => 'Snow Peak Views', 'icon' => 'fa-solid fa-mountain-sun'],
            ['name' => 'Heated Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Fireplace Suite', 'icon' => 'fa-solid fa-fire'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Alpine Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Best Snow Season',
        'price' => 7899,
        'original_price' => 10500,
    ],
    [
        'id' => 'HTL-MNL-06',
        'name' => 'Span Resort & Beas Riverfront',
        'location_tag' => 'manali',
        'location_text' => 'Kullu-Manali Highway, Beas Valley',
        'star_badge' => '5★ Riverside',
        'star_class' => 'bg-sky-600/90 text-white border-sky-400',
        'rating' => '4.8',
        'reviews' => '210',
        'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Span Resort Riverside Valley',
        'amenities' => [
            ['name' => 'Beas Riverfront', 'icon' => 'fa-solid fa-water'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Helipad & Spa', 'icon' => 'fa-solid fa-spa'],
            ['name' => 'Pine Forest Walk', 'icon' => 'fa-solid fa-tree'],
            ['name' => 'Free Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Instant Booking',
        'price' => 9499,
        'original_price' => 12000,
    ],
    [
        'id' => 'HTL-MNL-07',
        'name' => 'Larisa Apple Orchards & Resort',
        'location_tag' => 'manali',
        'location_text' => 'Haripur, Manali Valley',
        'star_badge' => '4★ Boutique',
        'star_class' => 'bg-brand-600/90 text-white border-brand-400',
        'rating' => '4.8',
        'reviews' => '175',
        'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Larisa Apple Orchards Resort',
        'amenities' => [
            ['name' => 'Apple Orchard View', 'icon' => 'fa-solid fa-tree'],
            ['name' => 'Jacuzzi Suite', 'icon' => 'fa-solid fa-hot-tub-person'],
            ['name' => 'Organic Dining', 'icon' => 'fa-solid fa-utensils'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'High-Speed Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Pay at Hotel',
        'price' => 6199,
        'original_price' => 8400,
    ],
    [
        'id' => 'HTL-MNL-08',
        'name' => 'Snow Valley Alpine Chalet',
        'location_tag' => 'manali',
        'location_text' => 'Log Huts Area, Manali',
        'star_badge' => '4★ Mountain Chalet',
        'star_class' => 'bg-emerald-600/90 text-white border-emerald-400',
        'rating' => '4.7',
        'reviews' => '195',
        'image' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Snow Valley Alpine Manali',
        'amenities' => [
            ['name' => 'Mountain Balcony', 'icon' => 'fa-solid fa-mountain-sun'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Game Lounge', 'icon' => 'fa-solid fa-gamepad'],
            ['name' => 'Bonfire Nights', 'icon' => 'fa-solid fa-fire-flame-curved'],
            ['name' => 'Free Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Guaranteed Stay',
        'price' => 4599,
        'original_price' => 6000,
    ],

    // 3. JAIPUR HOTELS (4 STAYS)
    [
        'id' => 'HTL-JPR-09',
        'name' => 'Rambagh Palace Grand Heritage',
        'location_tag' => 'jaipur',
        'location_text' => 'Bhawani Singh Road, Jaipur',
        'star_badge' => '5★ Royal Palace',
        'star_class' => 'bg-amber-500/90 text-white border-amber-400',
        'rating' => '5.0',
        'reviews' => '520',
        'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Rambagh Palace Jaipur Court',
        'amenities' => [
            ['name' => 'Royal Butler Service', 'icon' => 'fa-solid fa-crown'],
            ['name' => 'Peacock Courtyard', 'icon' => 'fa-solid fa-feather'],
            ['name' => 'Jiva Grand Spa', 'icon' => 'fa-solid fa-spa'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Vintage Car Tour', 'icon' => 'fa-solid fa-car-side']
        ],
        'cancellation' => 'Free Cancellation • Royal Experience',
        'price' => 22999,
        'original_price' => 30000,
    ],
    [
        'id' => 'HTL-JPR-10',
        'name' => 'The Leela Palace Heritage Sanctuary',
        'location_tag' => 'jaipur',
        'location_text' => 'Delhi-Jaipur Highway, Amber, Jaipur',
        'star_badge' => '5★ Palace Resort',
        'star_class' => 'bg-amber-500/90 text-white border-amber-400',
        'rating' => '4.9',
        'reviews' => '310',
        'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
        'alt' => 'The Leela Palace Amber Fort View',
        'amenities' => [
            ['name' => 'Heritage Courtyard', 'icon' => 'fa-solid fa-chess-rook'],
            ['name' => 'Royal Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Amber Fort View', 'icon' => 'fa-solid fa-eye'],
            ['name' => 'High-Speed Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Pay at Hotel',
        'price' => 14999,
        'original_price' => 19500,
    ],
    [
        'id' => 'HTL-JPR-11',
        'name' => 'Jai Mahal Palace Mughal Retreat',
        'location_tag' => 'jaipur',
        'location_text' => 'Jacob Road, Civil Lines, Jaipur',
        'star_badge' => '5★ Mughal Palace',
        'star_class' => 'bg-purple-600/90 text-white border-purple-400',
        'rating' => '4.9',
        'reviews' => '240',
        'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Jai Mahal Palace Gardens',
        'amenities' => [
            ['name' => 'Mughal Gardens', 'icon' => 'fa-solid fa-tree'],
            ['name' => 'Fine Dining', 'icon' => 'fa-solid fa-utensils'],
            ['name' => 'Outdoor Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Royal Spa', 'icon' => 'fa-solid fa-spa']
        ],
        'cancellation' => 'Free Cancellation • Instant Booking',
        'price' => 11499,
        'original_price' => 15000,
    ],
    [
        'id' => 'HTL-JPR-12',
        'name' => 'Samode Haveli Boutique Haven',
        'location_tag' => 'jaipur',
        'location_text' => 'Gangapole, Old Pink City, Jaipur',
        'star_badge' => '4★ Historic Haveli',
        'star_class' => 'bg-brand-600/90 text-white border-brand-400',
        'rating' => '4.8',
        'reviews' => '190',
        'image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Samode Haveli Old Jaipur Pool',
        'amenities' => [
            ['name' => 'Historic Courtyard', 'icon' => 'fa-solid fa-landmark'],
            ['name' => 'Jacuzzi Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Sunset Terrace', 'icon' => 'fa-solid fa-sun'],
            ['name' => 'Free High-Speed Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Pay at Hotel',
        'price' => 7499,
        'original_price' => 9800,
    ],

    // 4. KERALA HOTELS (4 STAYS)
    [
        'id' => 'HTL-KRL-13',
        'name' => 'Kumarakom Lake Resort & Villas',
        'location_tag' => 'kerala',
        'location_text' => 'Vembanad Lake Banks, Kumarakom',
        'star_badge' => '5★ Backwater Sanctuary',
        'star_class' => 'bg-emerald-600/90 text-white border-emerald-400',
        'rating' => '4.9',
        'reviews' => '380',
        'image' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Kumarakom Lake Resort Pool Villa',
        'amenities' => [
            ['name' => 'Meandering Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Heritage Villas', 'icon' => 'fa-solid fa-house-chimney'],
            ['name' => 'Sunset Cruise', 'icon' => 'fa-solid fa-sailboat'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Ayurvedic Spa', 'icon' => 'fa-solid fa-spa']
        ],
        'cancellation' => 'Free Cancellation • Kerala Special',
        'price' => 12999,
        'original_price' => 16500,
    ],
    [
        'id' => 'HTL-KRL-14',
        'name' => 'Blanket Luxury Resort & Spa',
        'location_tag' => 'kerala',
        'location_text' => 'Attukad Waterfalls, Munnar Hills',
        'star_badge' => '5★ Valley Resort',
        'star_class' => 'bg-emerald-600/90 text-white border-emerald-400',
        'rating' => '4.9',
        'reviews' => '290',
        'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Blanket Resort Munnar Waterfall View',
        'amenities' => [
            ['name' => 'Waterfall View', 'icon' => 'fa-solid fa-eye'],
            ['name' => 'Infinity Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Tea Tasting Tour', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-utensils'],
            ['name' => 'Mountain Spa', 'icon' => 'fa-solid fa-spa']
        ],
        'cancellation' => 'Free Cancellation • Instant Booking',
        'price' => 6499,
        'original_price' => 8500,
    ],
    [
        'id' => 'HTL-KRL-15',
        'name' => 'Brunton Boatyard Waterfront',
        'location_tag' => 'kerala',
        'location_text' => 'Fort Kochi Harbor, Kochi',
        'star_badge' => '4★ Colonial Heritage',
        'star_class' => 'bg-brand-600/90 text-white border-brand-400',
        'rating' => '4.8',
        'reviews' => '185',
        'image' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Brunton Boatyard Fort Kochi',
        'amenities' => [
            ['name' => 'Harbor View', 'icon' => 'fa-solid fa-sailboat'],
            ['name' => 'Daily Sunset Cruise', 'icon' => 'fa-solid fa-anchor'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Colonial Dining', 'icon' => 'fa-solid fa-utensils'],
            ['name' => 'High-Speed Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Pay at Hotel',
        'price' => 5899,
        'original_price' => 7500,
    ],
    [
        'id' => 'HTL-KRL-16',
        'name' => 'The Leela Kovalam Cliff Retreat',
        'location_tag' => 'kerala',
        'location_text' => 'Kovalam Beach Cliff, Trivandrum',
        'star_badge' => '5★ Cliffside Resort',
        'star_class' => 'bg-sky-600/90 text-white border-sky-400',
        'rating' => '4.9',
        'reviews' => '320',
        'image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
        'alt' => 'The Leela Kovalam Cliff Infinity Pool',
        'amenities' => [
            ['name' => 'Cliff Infinity Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Private Beach', 'icon' => 'fa-solid fa-umbrella-beach'],
            ['name' => 'Ayurveda Spa', 'icon' => 'fa-solid fa-spa'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Sunset Sun Deck', 'icon' => 'fa-solid fa-sun']
        ],
        'cancellation' => 'Free Cancellation • Ocean View Guaranteed',
        'price' => 11200,
        'original_price' => 14800,
    ],

    // 5. DUBAI HOTELS (4 STAYS)
    [
        'id' => 'HTL-DXB-17',
        'name' => 'Atlantis The Palm Island Resort',
        'location_tag' => 'dubai',
        'location_text' => 'Palm Jumeirah, Dubai',
        'star_badge' => '5★ Iconic Resort',
        'star_class' => 'bg-sky-600/90 text-white border-sky-400',
        'rating' => '5.0',
        'reviews' => '650',
        'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Atlantis The Palm Dubai Resort',
        'amenities' => [
            ['name' => 'Aquaventure Pass', 'icon' => 'fa-solid fa-water'],
            ['name' => 'Lost Chambers', 'icon' => 'fa-solid fa-fish'],
            ['name' => 'Private Beach', 'icon' => 'fa-solid fa-umbrella-beach'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Michelin Dining', 'icon' => 'fa-solid fa-utensils']
        ],
        'cancellation' => 'Free Cancellation • Guaranteed Stay',
        'price' => 24999,
        'original_price' => 31000,
    ],
    [
        'id' => 'HTL-DXB-18',
        'name' => 'Address Downtown Burj View',
        'location_tag' => 'dubai',
        'location_text' => 'Downtown Dubai • Mall Attached',
        'star_badge' => '5★ Luxury Downtown',
        'star_class' => 'bg-amber-500/90 text-white border-amber-400',
        'rating' => '4.9',
        'reviews' => '410',
        'image' => 'https://images.unsplash.com/photo-1549294413-26f195200c16?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Address Downtown Dubai View',
        'amenities' => [
            ['name' => 'Burj Khalifa View', 'icon' => 'fa-solid fa-building'],
            ['name' => 'Infinity Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Fine Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Direct Mall Access', 'icon' => 'fa-solid fa-bag-shopping'],
            ['name' => 'High-Speed Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Instant Voucher',
        'price' => 19499,
        'original_price' => 25000,
    ],
    [
        'id' => 'HTL-DXB-19',
        'name' => 'Rove Downtown Dubai City Stay',
        'location_tag' => 'dubai',
        'location_text' => 'Zabeel 2, Downtown Dubai',
        'star_badge' => '4★ Modern City',
        'star_class' => 'bg-brand-600/90 text-white border-brand-400',
        'rating' => '4.8',
        'reviews' => '280',
        'image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Rove Downtown Modern Room',
        'amenities' => [
            ['name' => 'Rooftop Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Mall Shuttle', 'icon' => 'fa-solid fa-van-shuttle'],
            ['name' => '24x7 Gym & Café', 'icon' => 'fa-solid fa-dumbbell'],
            ['name' => 'Free High-Speed Wi-Fi', 'icon' => 'fa-solid fa-wifi'],
            ['name' => 'Laundromat', 'icon' => 'fa-solid fa-shirt']
        ],
        'cancellation' => 'Free Cancellation • Best Value 2026',
        'price' => 6299,
        'original_price' => 8200,
    ],
    [
        'id' => 'HTL-DXB-20',
        'name' => 'Jumeirah Al Qasr Luxury Beach',
        'location_tag' => 'dubai',
        'location_text' => 'Madinat Jumeirah, Dubai',
        'star_badge' => '5★ Royal Palace',
        'star_class' => 'bg-amber-500/90 text-white border-amber-400',
        'rating' => '5.0',
        'reviews' => '390',
        'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Jumeirah Al Qasr Resort Beach',
        'amenities' => [
            ['name' => 'Private Souk Boat', 'icon' => 'fa-solid fa-sailboat'],
            ['name' => 'Private Beach', 'icon' => 'fa-solid fa-umbrella-beach'],
            ['name' => '50+ Dining Options', 'icon' => 'fa-solid fa-utensils'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Talise Spa', 'icon' => 'fa-solid fa-spa']
        ],
        'cancellation' => 'Free Cancellation • Arabian Luxury',
        'price' => 28500,
        'original_price' => 36000,
    ],

    // 6. UDAIPUR HOTELS (4 STAYS)
    [
        'id' => 'HTL-UDP-21',
        'name' => 'The Oberoi Udaivilas Palace',
        'location_tag' => 'udaipur',
        'location_text' => 'Lake Pichola Banks, Udaipur',
        'star_badge' => '5★ Grand Palace',
        'star_class' => 'bg-amber-500/90 text-white border-amber-400',
        'rating' => '5.0',
        'reviews' => '490',
        'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
        'alt' => 'The Oberoi Udaivilas Pool',
        'amenities' => [
            ['name' => 'Moat Infinity Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Boat Arrival', 'icon' => 'fa-solid fa-sailboat'],
            ['name' => 'Royal Courtyard', 'icon' => 'fa-solid fa-crown'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Oberoi Spa', 'icon' => 'fa-solid fa-spa']
        ],
        'cancellation' => 'Free Cancellation • World #1 Hotel',
        'price' => 28999,
        'original_price' => 36000,
    ],
    [
        'id' => 'HTL-UDP-22',
        'name' => 'Taj Lake Palace Island Sanctuary',
        'location_tag' => 'udaipur',
        'location_text' => 'Pichola Lake Island, Udaipur',
        'star_badge' => '5★ Island Palace',
        'star_class' => 'bg-purple-600/90 text-white border-purple-400',
        'rating' => '5.0',
        'reviews' => '580',
        'image' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Taj Lake Palace Island Udaipur',
        'amenities' => [
            ['name' => 'Floating Palace', 'icon' => 'fa-solid fa-gem'],
            ['name' => 'Jiva Spa Boat', 'icon' => 'fa-solid fa-spa'],
            ['name' => 'Private Boat Access', 'icon' => 'fa-solid fa-sailboat'],
            ['name' => 'Royal Dining', 'icon' => 'fa-solid fa-utensils'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer']
        ],
        'cancellation' => 'Free Cancellation • Lifetime Stay',
        'price' => 26500,
        'original_price' => 34000,
    ],
    [
        'id' => 'HTL-UDP-23',
        'name' => 'Fateh Garh Heritage Sanctuary',
        'location_tag' => 'udaipur',
        'location_text' => 'Sisarma Hills, Udaipur',
        'star_badge' => '4★ Hilltop Palace',
        'star_class' => 'bg-brand-600/90 text-white border-brand-400',
        'rating' => '4.8',
        'reviews' => '220',
        'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Fateh Garh Hilltop Pool Udaipur',
        'amenities' => [
            ['name' => 'Dual Hilltop Pools', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Vintage Cars Tour', 'icon' => 'fa-solid fa-car-side'],
            ['name' => 'Buffet Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Panoramic Sunset Deck', 'icon' => 'fa-solid fa-sun'],
            ['name' => 'Free Wi-Fi', 'icon' => 'fa-solid fa-wifi']
        ],
        'cancellation' => 'Free Cancellation • Pay at Hotel',
        'price' => 5499,
        'original_price' => 7200,
    ],
    [
        'id' => 'HTL-UDP-24',
        'name' => 'The Leela Palace Lake Pichola',
        'location_tag' => 'udaipur',
        'location_text' => 'Lake Pichola Waterfront, Udaipur',
        'star_badge' => '5★ Lakefront Palace',
        'star_class' => 'bg-amber-500/90 text-white border-amber-400',
        'rating' => '4.9',
        'reviews' => '410',
        'image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80',
        'alt' => 'The Leela Palace Udaipur Lake',
        'amenities' => [
            ['name' => 'Lake View Suites', 'icon' => 'fa-solid fa-eye'],
            ['name' => 'ESPA Luxury Spa', 'icon' => 'fa-solid fa-spa'],
            ['name' => 'Private Jetty', 'icon' => 'fa-solid fa-sailboat'],
            ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
            ['name' => 'Pichola Dining', 'icon' => 'fa-solid fa-utensils']
        ],
        'cancellation' => 'Free Cancellation • Royal Lake Arrival',
        'price' => 24500,
        'original_price' => 32000,
    ]
];

// Dynamically fetch and merge hotels added via Admin Panel
if (function_exists('getDBConnection')) {
    $pdoHotels = getDBConnection();
} else {
    require_once __DIR__ . '/../config/db.php';
    $pdoHotels = getDBConnection();
}

if ($pdoHotels) {
    try {
        $dbHotels = $pdoHotels->query("SELECT * FROM `hotels` WHERE `status` = 'active' ORDER BY `id` DESC")->fetchAll();
        if (!empty($dbHotels)) {
            $dynamicHotels = [];
            $cityCounts = [];
            $cityImages = [];

            foreach ($dbHotels as $dbh) {
                // Amenities JSON parse
                $ams = [];
                if (!empty($dbh['amenities'])) {
                    $decodedAm = json_decode($dbh['amenities'], true);
                    if (is_array($decodedAm)) {
                        foreach ($decodedAm as $da) {
                            if (is_array($da) && isset($da['name'])) {
                                $ams[] = ['name' => $da['name'], 'icon' => $da['icon'] ?? 'fa-solid fa-circle-check'];
                            } elseif (is_string($da)) {
                                $ams[] = ['name' => $da, 'icon' => 'fa-solid fa-circle-check'];
                            }
                        }
                    }
                }
                if (empty($ams)) {
                    $ams = [
                        ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer'],
                        ['name' => 'Free Wi-Fi', 'icon' => 'fa-solid fa-wifi']
                    ];
                }

                $cityName = !empty($dbh['city']) ? trim($dbh['city']) : 'Other';
                $locTag = strtolower(trim(preg_replace('/[^A-Za-z0-9]/', '', $cityName)));
                if (empty($locTag)) $locTag = 'all';

                if (!isset($cityCounts[$locTag])) {
                    $cityCounts[$locTag] = ['name' => $cityName, 'count' => 0];
                }
                $cityCounts[$locTag]['count']++;

                $img = !empty($dbh['featured_image']) ? $dbh['featured_image'] : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80';
                if (!isset($cityImages[$locTag])) {
                    $cityImages[$locTag] = $img;
                }

                $hotelCancelText = 'Free Cancellation • Instant Booking';
                if (!empty($dbh['policies'])) {
                    $decPol = json_decode($dbh['policies'], true);
                    if (is_array($decPol) && !empty($decPol['summary'])) {
                        $hotelCancelText = $decPol['summary'];
                    } elseif (!str_starts_with(trim($dbh['policies']), '{')) {
                        $hotelCancelText = $dbh['policies'];
                    } elseif (preg_match('/"summary"\s*:\s*"([^"]+)"/', $dbh['policies'], $m)) {
                        $hotelCancelText = stripslashes($m[1]);
                    }
                }

                $dynamicHotels[] = [
                    'id' => 'HTL-DB-' . $dbh['id'],
                    'name' => $dbh['name'],
                    'location_tag' => $locTag,
                    'location_text' => (!empty($dbh['city']) ? $dbh['city'] : 'India') . (!empty($dbh['country']) ? ', ' . $dbh['country'] : ''),
                    'star_badge' => $dbh['badge'] ?: ($dbh['star_rating'] . '★ Luxury'),
                    'star_class' => 'bg-amber-500/90 text-white border-amber-400',
                    'rating' => number_format((float)$dbh['star_rating'], 1),
                    'reviews' => '100+',
                    'image' => $img,
                    'alt' => $dbh['name'],
                    'amenities' => $ams,
                    'cancellation' => $hotelCancelText,
                    'price' => (float)$dbh['starting_price'],
                    'original_price' => (float)$dbh['original_price'] ?: ((float)$dbh['starting_price'] * 1.25),
                ];
            }
            // Use 100% dynamic hotels from database
            $hotelsList = $dynamicHotels;

            // Dynamically generate destination filter track
            $newDestinations = [
                [
                    'id' => 'all',
                    'name' => 'All Locations',
                    'count' => count($hotelsList) . '+ Stays',
                    'image' => $hotelsList[0]['image'] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=400&q=80',
                    'icon' => 'fa-solid fa-hotel',
                ]
            ];
            foreach ($cityCounts as $tag => $data) {
                $newDestinations[] = [
                    'id' => $tag,
                    'name' => $data['name'],
                    'count' => $data['count'] . ' Curated Stays',
                    'image' => $cityImages[$tag] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=400&q=80',
                    'icon' => 'fa-solid fa-map-pin',
                ];
            }
            $hotelDestinations = $newDestinations;
        }
    } catch (Exception $e) {
        // Fallback silently to static list
    }
}
?>
<!-- ==========================================
     HANDPICKED HOTELS & RESORTS (2-ROW SLIDER COMPONENT)
     - Step 1: Visual Destination Selector Track at Top (Goa, Manali, Jaipur, etc.)
     - Step 2: 2-Row Horizontal Slider with Lightweight Suite Cards (Both Rows Slide)
     - Continuous Marquee Slider for Amenities with Font Awesome 6 Icons
     - 2 Full Cards + 1 Peeking Card Layout on Desktop (Zero Heavy Blocks)
     - 100% Flat, Border-First (Zero Shadows)
     - Font Awesome 6 Icons Only (Zero Emojis)
=========================================== -->
<section id="hotels-section" class="py-16 md:py-20 px-4 sm:px-8 xl:px-12 bg-slate-50/60 border-b border-slate-200">
    <div class="max-w-7xl mx-auto">

        <!-- 1. Section Header (Matching Domestic & Intl Style with Inline Capsule Image) -->
        <div class="max-w-3xl mx-auto text-center space-y-3 mb-10">
            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <i class="fa-solid fa-hotel text-brand-600"></i>
                <span>Handpicked Luxury Stays & Resorts</span>
            </div>

            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                <span>Top Hotels & Resorts </span>
                <span class="inline-block align-middle mx-1.5 sm:mx-2.5">
                    <img src="assets/images/banner/heading-hotel-01.jpg" 
                         alt="Resort Infinity Pool" 
                         class="h-8 sm:h-11 md:h-12 w-auto rounded-full object-cover border border-slate-200 hover:scale-105 transition-transform duration-300 select-none">
                </span>
                <span class="text-brand-600">for Every Journey</span>
            </h2>

            <p class="text-xs sm:text-sm text-slate-500 font-normal max-w-xl mx-auto">
               Find comfortable stays for every kind of trip, from budget-friendly hotels to premium properties in your favourite destinations.
            </p>
        </div>

        <!-- 2. Step 1: Visual Destination Selector Track (Locations Dikhenge) -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-3 px-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center">
                    <i class="fa-solid fa-map-location-dot text-brand-600 mr-2 text-sm"></i>
                    <span>Select Destination To Filter Stays</span>
                </span>
                <span class="text-xs font-semibold text-brand-600 hidden sm:inline" id="activeLocationCount">
                    Showing all 24 top-rated stays
                </span>
            </div>

            <!-- Horizontal Scrollable Destination Cards Track -->
            <div class="flex items-center space-x-3 sm:space-x-4 overflow-x-auto pb-2 pt-1 px-1 scrollbar-none" id="hotelDestinationsTrack">
                <?php foreach ($hotelDestinations as $index => $dest): ?>
                    <button type="button" 
                            data-location-id="<?= htmlspecialchars($dest['id']) ?>" 
                            class="hotel-dest-btn flex items-center space-x-3 p-2 pr-4 sm:pr-5 rounded-2xl border transition-all shrink-0 select-none <?= $index === 0 ? 'bg-brand-600 text-white border-brand-600 font-bold active-dest' : 'bg-white text-slate-700 border-slate-200 hover:border-brand-500 hover:bg-slate-50 font-semibold' ?>">
                        
                        <!-- Destination Thumbnail Image -->
                        <span class="w-11 h-11 rounded-xl overflow-hidden shrink-0 border border-black/10 relative">
                            <img src="<?= htmlspecialchars($dest['image']) ?>" 
                                 alt="<?= htmlspecialchars($dest['name']) ?>" 
                                 class="w-full h-full object-cover">
                        </span>

                        <div class="text-left">
                            <h4 class="text-xs sm:text-sm font-bold leading-tight block"><?= htmlspecialchars($dest['name']) ?></h4>
                            <span class="text-[10px] opacity-80 font-normal block mt-0.5"><?= htmlspecialchars($dest['count']) ?></span>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 3. Step 2: TWO-ROW SLIDER CONTAINER (Both Rows Slide Together, Lightweight UI) -->
        <div class="relative hotel-slider-container group">
            
            <!-- Prev Arrow (Hover-only on Desktop) -->
            <button type="button" 
                    id="hotelSlidePrev" 
                    aria-label="Previous Stays" 
                    class="absolute -left-3 sm:-left-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white text-slate-800 border border-slate-300 flex items-center justify-center transition-all hover:bg-brand-600 hover:text-white hover:border-brand-600 disabled:opacity-30 disabled:pointer-events-none">
                <i class="fa-solid fa-chevron-left text-sm"></i>
            </button>

            <!-- 2-Row Slider Track (Both Rows Slide Horizontally) -->
            <div class="hotels-slider-track py-2 px-1" id="hotelsSlider">
                <?php foreach ($hotelsList as $hotel): ?>
                    <div class="hotel-card bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition-all p-2 sm:p-3 flex flex-row gap-2 sm:gap-3.5 group relative select-none shrink-0 h-[196px] sm:h-[184px] justify-between" data-location="<?= htmlspecialchars($hotel['location_tag']) ?>">
                        
                        <!-- Left: Photo Container (Fixed Width) -->
                        <div class="w-24 sm:w-44 h-full rounded-xl overflow-hidden relative shrink-0 border border-slate-100 bg-slate-100">
                            <img src="<?= htmlspecialchars($hotel['image']) ?>" 
                                 alt="<?= htmlspecialchars($hotel['alt']) ?>" 
                                 loading="lazy" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            <!-- Star Tier Tag (Hidden on Mobile, Visible on Desktop) -->
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-bold <?= $hotel['star_class'] ?> border backdrop-blur-xs hidden sm:flex items-center space-x-1">
                                <i class="fa-solid fa-crown text-[9px]"></i>
                                <span><?= htmlspecialchars($hotel['star_badge']) ?></span>
                            </span>

                            <!-- Wishlist Heart Button -->
                            <button type="button" aria-label="Save to Wishlist" class="wishlist-btn absolute bottom-1.5 sm:bottom-2 right-1.5 sm:right-2 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-white/95 text-slate-600 hover:text-rose-500 hover:bg-white flex items-center justify-center transition border border-slate-200">
                                <i class="fa-regular fa-heart text-[10px] sm:text-xs"></i>
                            </button>
                        </div>

                        <!-- Right: Card Details Container -->
                        <div class="flex-1 flex flex-col justify-between min-w-0 py-0.5">
                            
                            <!-- Row 1: Location & Rating -->
                            <div class="flex items-center justify-between gap-1">
                                <div class="text-[10px] sm:text-[11px] font-bold text-brand-600 flex items-center space-x-1 truncate min-w-0">
                                    <i class="fa-solid fa-location-dot text-[9px] sm:text-[10px] shrink-0"></i>
                                    <span class="truncate"><?= htmlspecialchars($hotel['location_text']) ?></span>
                                </div>
                                <span class="inline-flex items-center space-x-1 px-1 sm:px-1.5 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-700 text-[10px] sm:text-[11px] font-black shrink-0">
                                    <i class="fa-solid fa-star text-[9px] sm:text-[10px] text-amber-500"></i>
                                    <span><?= htmlspecialchars($hotel['rating']) ?></span>
                                </span>
                            </div>

                            <!-- Row 2: Hotel Name -->
                            <h3 class="text-xs sm:text-base font-extrabold text-slate-900 group-hover:text-brand-600 transition truncate leading-tight" title="<?= htmlspecialchars($hotel['name']) ?>">
                                <?= htmlspecialchars($hotel['name']) ?>
                            </h3>

                            <!-- Row 3: Amenities Continuous Marquee Slider (Seamless Loop) -->
                            <div class="hotel-amenities-slider py-0.5 sm:py-1 px-1 sm:px-1.5 bg-slate-50/80 border border-slate-100 rounded-lg">
                                <div class="hotel-amenities-track">
                                    <!-- Set 1 -->
                                    <?php foreach ($hotel['amenities'] as $amenity): ?>
                                        <span class="inline-flex items-center space-x-1 text-[9.5px] sm:text-[10.5px] font-semibold text-slate-700 whitespace-nowrap shrink-0">
                                            <i class="<?= htmlspecialchars($amenity['icon']) ?> text-brand-600 text-[10px] sm:text-[11px] shrink-0"></i>
                                            <span><?= htmlspecialchars($amenity['name']) ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                    <!-- Set 2 (Seamless loop) -->
                                    <?php foreach ($hotel['amenities'] as $amenity): ?>
                                        <span class="inline-flex items-center space-x-1 text-[9.5px] sm:text-[10.5px] font-semibold text-slate-700 whitespace-nowrap shrink-0">
                                            <i class="<?= htmlspecialchars($amenity['icon']) ?> text-brand-600 text-[10px] sm:text-[11px] shrink-0"></i>
                                            <span><?= htmlspecialchars($amenity['name']) ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Row 4: Free Cancellation Assurance -->
                            <div class="text-[9.5px] sm:text-[10px] font-bold text-emerald-600 flex items-center space-x-1 truncate">
                                <i class="fa-solid fa-shield-halved text-[8.5px] sm:text-[9px] shrink-0"></i>
                                <span class="truncate"><?= htmlspecialchars($hotel['cancellation']) ?></span>
                            </div>

                            <!-- Row 5: Price & CTA Action -->
                            <div class="flex items-center justify-between pt-1 border-t border-slate-100 gap-1.5 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-baseline min-w-0 leading-tight">
                                    <div class="flex items-baseline space-x-1">
                                        <span class="text-sm sm:text-base font-black text-slate-900 leading-none">₹<?= number_format($hotel['price']) ?></span>
                                        <span class="text-[10px] sm:text-[11px] text-slate-400 line-through">₹<?= number_format($hotel['original_price']) ?></span>
                                    </div>
                                    <span class="text-[9px] sm:text-[10px] text-slate-400 font-medium sm:ml-1">/ night</span>
                                </div>

                                <a href="hotel-details.php?id=<?= urlencode($hotel['id']) ?>" 
                                   class="book-hotel-btn inline-flex items-center space-x-1 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-brand-50 hover:bg-brand-600 text-brand-700 hover:text-white border border-brand-200 hover:border-brand-600 text-[11px] sm:text-xs font-bold transition-all group/btn shrink-0"
                                   data-hotel-id="<?= htmlspecialchars($hotel['id']) ?>"
                                   data-hotel-name="<?= htmlspecialchars($hotel['name']) ?>"
                                   data-location="<?= htmlspecialchars($hotel['location_tag']) ?>">
                                    <span>View Rooms</span>
                                    <i class="fa-solid fa-arrow-right text-[9px] sm:text-[10px] group-hover/btn:translate-x-0.5 transition-transform"></i>
                                </a>
                            </div>

                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Next Arrow (Hover-only on Desktop) -->
            <button type="button" 
                    id="hotelSlideNext" 
                    aria-label="Next Stays" 
                    class="absolute -right-3 sm:-right-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white text-slate-800 border border-slate-300 flex items-center justify-center transition-all hover:bg-brand-600 hover:text-white hover:border-brand-600 disabled:opacity-30 disabled:pointer-events-none">
                <i class="fa-solid fa-chevron-right text-sm"></i>
            </button>

        </div>

    </div>
</section>
