CREATE TABLE OrdersItems
(
    id         int auto_increment,
    order_id   int,
    product_id int, 
    quantity int            default 0 , 
    unit_price      decimal(12, 2) default 0.00,
    
    foreign key (order_id) references Orders (id)
    foreign key (product_id) references Products (id),
    
    UNIQUE KEY (product_id,  order_id)
)

