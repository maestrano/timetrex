alter table request change authorized authorized tinyint default 0;
update request set authorized = 0 where authorized is null;
