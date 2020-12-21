CREATE TABLE Orders (
    id int auto_increment,
    user_id int,
    total_price decimal(12,2) default 0.00,
    created TIMESTAMP default current_timestamp,
    address VARCHAR(50),
    payment_method VARCHAR(60),
    primary key (id),
    foreign key (user_id) references Users (id)
)
