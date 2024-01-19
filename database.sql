create database lab_laravel_database;

use lab_laravel_database;

create table categories
(
    id varchar(100) not null primary key,
    name varchar(100) not null,
    description text,
    created_at timestamp
) engine innodb;

desc categories;

CREATE TABLE counters (
    id varchar(100) not null PRIMARY KEY,
    counter int not null default 0
) engine innodb;

insert into counters(id, counter) values ('sample', 0);

SELECT * FROM counters;

CREATE TABLE products (
    id varchar(100) not null primary key,
    name varchar(100) not null,
    description text null,
    price int not null,
    category_id varchar(100) not null,
    created_at timestamp not null default current_timestamp,
    constraint fk_category_id FOREIGN KEY(category_id) REFERENCES categories(id)
) engine innodb;

SELECT * FROM products;

DROP TABLE products;

DROP TABLE categories;

DROP TABLE counters;

SHOW TABLES;
