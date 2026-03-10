<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Add this line

return new class extends Migration
{
    public function up(): void
    {
        // We use a single string with all your tables
        // Note: I have ordered them so parent tables are created before child tables
        DB::unprepared("
            CREATE TABLE accounts (
                account_id INT PRIMARY KEY AUTO_INCREMENT,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100),
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(15),
                status ENUM('Active', 'Inactive') DEFAULT 'Active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE users (
                user_id INT PRIMARY KEY,
                dob DATE,
                FOREIGN KEY (user_id) REFERENCES accounts(account_id) ON DELETE CASCADE
            );

            CREATE TABLE admin (
                admin_id INT PRIMARY KEY,
                role ENUM('Super_Admin', 'Regional_Manager') NOT NULL,
                FOREIGN KEY (admin_id) REFERENCES accounts(account_id) ON DELETE CASCADE
            );

            CREATE TABLE postcode (
                pincode VARCHAR(10) PRIMARY KEY,
                city VARCHAR(50),
                state VARCHAR(50)
            );

            CREATE TABLE CUISINE (
                cuisine_id INT PRIMARY KEY AUTO_INCREMENT,
                cuisine_name VARCHAR(50) UNIQUE NOT NULL
            );

            CREATE TABLE user_address (
                address_id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                building_name VARCHAR(100),
                street VARCHAR(150) NOT NULL,
                postcode VARCHAR(10) NOT NULL,
                address_type ENUM('Home', 'Work', 'Other') NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (postcode) REFERENCES postcode(pincode)
            );

            CREATE TABLE RESTAURANT (
                restaurant_id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                phone VARCHAR(15),
                admin_id INT,
                status ENUM('Open', 'Closed') DEFAULT 'Open',
                preparation_time INT NOT NULL DEFAULT 15,
                FOREIGN KEY (admin_id) REFERENCES admin(admin_id)
            );


            CREATE TABLE RESTAURANT_ADDRESS (
                restaurant_address_id INT PRIMARY KEY AUTO_INCREMENT,
                restaurant_id INT NOT NULL,
                building_name VARCHAR(100),
                street VARCHAR(150) NOT NULL,
                postcode VARCHAR(10) NOT NULL,

                FOREIGN KEY (restaurant_id) REFERENCES RESTAURANT(restaurant_id) ON DELETE CASCADE,
                FOREIGN KEY (postcode) REFERENCES POSTCODE(pincode)
            );

            CREATE TABLE RESTAURANT_CUISINE (
                restaurant_id INT,
                cuisine_id INT,
                PRIMARY KEY (restaurant_id, cuisine_id),
                FOREIGN KEY (restaurant_id) REFERENCES RESTAURANT(restaurant_id) ON DELETE CASCADE,
                FOREIGN KEY (cuisine_id) REFERENCES CUISINE(cuisine_id) ON DELETE CASCADE
            );

            CREATE TABLE FOOD_ITEM (
                food_id INT PRIMARY KEY AUTO_INCREMENT,
                restaurant_id INT NOT NULL,
                food_name VARCHAR(100) NOT NULL,
                description TEXT,
                availability BOOLEAN DEFAULT TRUE,
                is_veg BOOLEAN NOT NULL,
                price DECIMAL(8,2) NOT NULL,
                FOREIGN KEY (restaurant_id) REFERENCES RESTAURANT(restaurant_id) ON DELETE CASCADE
            );
            
            CREATE TABLE orders (
                order_id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                restaurant_id INT,
                order_date DATETIME,
                total_amount DECIMAL(10,2),
                status ENUM('Placed','Preparing','Delivered','Cancelled'),
                FOREIGN KEY (user_id) REFERENCES users(user_id),
                FOREIGN KEY (restaurant_id) REFERENCES restaurant(restaurant_id)
            );

            CREATE TABLE order_item (
                order_item_id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT,
                food_id INT,
                quantity INT,
                price DECIMAL(8,2),
                FOREIGN KEY (order_id) REFERENCES orders(order_id),
                FOREIGN KEY (food_id) REFERENCES food_item(food_id)
            );


            CREATE TABLE payment (
                payment_id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT UNIQUE,
                amount DECIMAL(10,2) NOT NULL,
                payment_mode ENUM('UPI','Card','Cash','NetBanking'),
                payment_status ENUM('Pending','Success','Failed'),
                transaction_id VARCHAR(100) UNIQUE NOT NULL,
                payment_date DATETIME,
                FOREIGN KEY (order_id) REFERENCES orders(order_id)
            );



            CREATE TABLE review (
                review_id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT UNIQUE,
                review_date DATE,
                FOREIGN KEY (order_id) REFERENCES orders(order_id)
            );

            CREATE TABLE review_item (
                review_item_id INT PRIMARY KEY AUTO_INCREMENT,
                review_id INT NOT NULL,
                item_type ENUM('RESTAURANT', 'FOOD') NOT NULL,
                item_id INT NOT NULL,
                rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
                comment VARCHAR(255),
                UNIQUE (review_id, item_type, item_id),
                FOREIGN KEY (review_id) REFERENCES review(review_id) ON DELETE CASCADE
            );


            CREATE TABLE action_log (
                log_id INT PRIMARY KEY AUTO_INCREMENT,
                admin_id INT NOT NULL,
                action VARCHAR(100) NOT NULL,
                remark VARCHAR(255),
                action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (admin_id) REFERENCES admin(admin_id)
            );

            CREATE TABLE restaurant_request (
                request_id INT PRIMARY KEY AUTO_INCREMENT,
                restaurant_id INT NOT NULL,
                admin_id INT,
                request_date DATETIME,
                status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
                decision_date DATE,
                remarks VARCHAR(255),

                FOREIGN KEY (restaurant_id) REFERENCES restaurant(restaurant_id),
                FOREIGN KEY (admin_id) REFERENCES admin(admin_id)
            );

            CREATE TABLE delivery (
                delivery_id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT UNIQUE NOT NULL,
                delivery_status ENUM('Assigned', 'Out_for_Delivery', 'Delivered') NOT NULL,
                estimated_time INT,          -- in minutes
                delivered_at DATETIME,

                FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
            );

        ");
    }

    public function down(): void
    {
        // To undo, we drop tables in reverse order of dependency
        Schema::dropIfExists('food_item');
        Schema::dropIfExists('restaurant');
        Schema::dropIfExists('cuisine');
        Schema::dropIfExists('postcode');
        Schema::dropIfExists('admin');
        Schema::dropIfExists('users');
        Schema::dropIfExists('accounts');
    }
};