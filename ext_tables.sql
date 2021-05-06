--
-- tx_pagenotfoundhandling_history
--
CREATE TABLE tx_pagenotfoundhandling_history (
    uid int(11) unsigned NOT NULL auto_increment,
    time int(11) unsigned DEFAULT 0 NOT NULL,

    site_identifier varchar(255),
    rootpage_uid int(11) unsigned DEFAULT 0 NOT NULL,
    status_code smallint unsigned DEFAULT 0 NOT NULL,
    failure_reason varchar(255),
    request_uri varchar(1024) DEFAULT '' NOT NULL,
    referer_uri varchar(1024) DEFAULT '' NOT NULL,
    user_agent varchar(1024) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid)
);
