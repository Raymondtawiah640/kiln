<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$productName = trim($data['product_name'] ?? '');

if (!$productName) {
    echo json_encode(['success' => false, 'message' => 'Product name required']);
    exit;
}

// Array of different description templates (expand as needed)
$descriptions = [
    "Introducing the amazing {$productName}! Perfect for your needs, crafted with quality and care. Get yours today!",
    "Discover the unmatched excellence of {$productName}, designed to bring joy and comfort.",
    "Elevate your lifestyle with {$productName} – a blend of style and durability.",
    "{$productName} is here to transform your everyday experience with superior quality.",
    "Experience innovation and elegance combined in the new {$productName}. Don't miss out!",
    "Upgrade your essentials with the reliable and stylish {$productName}.",
    "Made for those who demand the best: {$productName} delivers on every front.",
    "The perfect fusion of functionality and design, meet {$productName}.",
    "Step up your game with {$productName}, crafted for excellence.",
    "Unleash the power of quality with {$productName} in your hands.",
    "Your search for perfection ends here with {$productName}.",
    "Crafted with passion, built for you — introducing {$productName}.",
    "Discover the future of comfort and style with {$productName}.",
    "Bring home the unmatched performance of {$productName} today.",
    "The innovative {$productName} is ready to redefine your standards.",
    "Trusted by many, loved by all — that's the promise of {$productName}.",
    "Designed to inspire and built to last: meet {$productName}.",
    "{$productName} combines cutting-edge tech with timeless design.",
    "Feel the difference with every use of {$productName}.",
    "Take a step ahead with the versatile {$productName}.",
    "Revolutionize your daily routine with the powerful {$productName}.",
    "{$productName} is more than a product — it's a lifestyle choice.",
    "Precision-engineered to exceed expectations, this is {$productName}.",
    "Discover unparalleled comfort and style with {$productName}.",
    "The essential {$productName} for modern living and endless possibilities.",
    "Experience the blend of innovation and tradition in {$productName}.",
    "Bold, beautiful, and built for you: introducing {$productName}.",
    "The {$productName} delivers exceptional value and outstanding quality.",
    "Every detail matters—experience the craftsmanship of {$productName}.",
    "Elevate your standards with the premium {$productName}.",
    "The ultimate companion for your adventures: {$productName}.",
];

// Select one random description from the array
$randomIndex = array_rand($descriptions);
$generatedDescription = $descriptions[$randomIndex];

echo json_encode(['success' => true, 'description' => $generatedDescription]);
?>
