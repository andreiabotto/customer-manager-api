<?php

namespace Tests\Unit\DTOs;

use App\DTOs\RegisterDTO;
use PHPUnit\Framework\TestCase;

class RegisterDTOTest extends TestCase
{
    public function test_register_dto_creation(): void
    {
        $dto = new RegisterDTO(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123'
        );

        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals('john@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }

    public function test_register_dto_from_request(): void
    {
        $requestData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret123'
        ];

        $dto = RegisterDTO::fromRequest($requestData);

        $this->assertEquals('Jane Doe', $dto->name);
        $this->assertEquals('jane@example.com', $dto->email);
        $this->assertEquals('secret123', $dto->password);
    }

    public function test_register_dto_to_array(): void
    {
        $dto = new RegisterDTO(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123'
        );

        $array = $dto->toArray();

        $this->assertEquals([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ], $array);
    }
}