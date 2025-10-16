<?php

namespace App\DTOs;

class ProductDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public float $price,
        public string $category,
        public string $image,
        public array $rating
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'],
            description: $data['description'],
            price: $data['price'],
            category: $data['category'],
            image: $data['image'],
            rating: $data['rating']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'category' => $this->category,
            'image' => $this->image,
            'rating' => $this->rating,
        ];
    }
}