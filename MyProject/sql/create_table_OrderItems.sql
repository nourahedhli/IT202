CREATE TABLE OrderItems (
    id int auto_increment,
    order_id int,
    product_id int,
    quantity int,
    unit_price decimal (12,2) default 0.00,
    primary key (id),
    foreign key (order_id) references Orders (id),
    foreign key (product_id) references Products (id)
)


