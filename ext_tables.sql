--
-- Table structure for table 'sys_domain'
--
CREATE TABLE sys_domain (
    tx_pagenotfoundhandling_enable tinyint(3) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_default404Page int(11) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_defaultTemplateFile tinytext,
    tx_pagenotfoundhandling_default403Page int(11) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_default403Header tinyint(4) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_ignoreLanguage tinyint(3) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_forceLanguage int(11) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_languageParam varchar(45) DEFAULT 'L' NOT NULL,
    tx_pagenotfoundhandling_passthroughContentTypeHeader tinyint(3) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_sendXForwardedForHeader tinyint(3) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_additionalHeaders text,
    tx_pagenotfoundhandling_digestAuthentication varchar(255) DEFAULT '' NOT NULL,
);

--
-- tx_pagenotfoundhandling_history
--
CREATE TABLE tx_pagenotfoundhandling_history (
    uid int(11) unsigned NOT NULL auto_increment,
    time int(11) unsigned DEFAULT 0 NOT NULL,

    site_identifier varchar(255),
    status_code smallint unsigned DEFAULT 0 NOT NULL,
    failure_reason varchar(255),
    request_uri varchar(1024) DEFAULT '' NOT NULL,
    referer_uri varchar(1024) DEFAULT '' NOT NULL,
    user_agent varchar(1024) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid)
);
