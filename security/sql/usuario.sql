create table if not exists `usuario` (
	`id` int(11) NOT NULL UNIQUE AUTO_INCREMENT,
	`nome` varchar(255) NOT NULL,
	`sobrenome` varchar(255),
	`email` varchar(255) NOT NULL,
	`ativo` boolean,
	`created` datetime,
	`modified` datetime,
	PRIMARY KEY (`id`)
);
