CREATE TABLE Orders
(
    id         int auto_increment,
    user_id    int,
    total_price      decimal(12, 2) default 0.00,
    created    datetime  default current_timestamp,
    address TEXT,
    primary key (id),
    payment_method varchar(60),
  
    foreign key (user_id) references Users (id),
    UNIQUE KEY (user_id)
)
