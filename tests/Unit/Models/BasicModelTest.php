<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Subscriber;

class BasicModelTest extends TestCase {
    public function test_subscriber_model_exists() {
        $subscriber = new Subscriber();
        $this->assertInstanceOf(Subscriber::class, $subscriber);
    }

    public function test_subscriber_has_fillable_fields() {
        $subscriber = new Subscriber();
        $fillable = $subscriber->getFillable();

        $this->assertIsArray($fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('email', $fillable);
    }

    public function test_subscriber_can_set_attributes() {
        $subscriber = new Subscriber();
        $subscriber->name = 'John Doe';
        $subscriber->phone = '+1234567890';
        $subscriber->email = 'john@example.com';

        $this->assertEquals('John Doe', $subscriber->name);
        $this->assertEquals('+1234567890', $subscriber->phone);
        $this->assertEquals('john@example.com', $subscriber->email);
    }

    public function test_subscriber_can_have_different_statuses() {
        $statuses = ['active', 'bad'];

        foreach ($statuses as $status) {
            $subscriber = new Subscriber();
            $subscriber->status = $status;
            $this->assertEquals($status, $subscriber->status);
        }
    }

    public function test_subscriber_can_have_balance() {
        $subscriber = new Subscriber();
        $subscriber->balance = 1500.50;

        $this->assertEquals(1500.50, $subscriber->balance);
        $this->assertIsNumeric($subscriber->balance);
    }
}
