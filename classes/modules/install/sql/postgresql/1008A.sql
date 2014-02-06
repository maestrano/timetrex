alter table "users" add column group_id integer DEFAULT 0;
alter table "users" add column finger_print_1 text DEFAULT NULL;
alter table "users" add column finger_print_2 text DEFAULT NULL;
alter table "users" add column finger_print_3 text DEFAULT NULL;
alter table "users" add column finger_print_4 text DEFAULT NULL;

CREATE TABLE user_group (
    id serial NOT NULL,
    company_id integer NOT NULL,
    name character varying NOT NULL,
    created_date integer,
    created_by integer,
    updated_date integer,
    updated_by integer,
    deleted_date integer,
    deleted_by integer,
    deleted boolean DEFAULT false NOT NULL
);

CREATE TABLE user_group_tree (
    tree_id integer DEFAULT 0 NOT NULL,
    parent_id integer DEFAULT 0 NOT NULL,
    object_id integer DEFAULT 0 NOT NULL,
    left_id bigint DEFAULT 0 NOT NULL,
    right_id bigint DEFAULT 0 NOT NULL
);