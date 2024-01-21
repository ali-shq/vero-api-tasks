create table if not exists construction_stages
(
    id            integer primary key,
    name          varchar(255)              not null,
    start_date    datetime                  not null,
    end_date      datetime,
    duration      float,
    duration_unit varchar(50),
    color         varchar(50),
    external_id   nvarchar(255),
    status        varchar(50) default 'NEW' not null
)