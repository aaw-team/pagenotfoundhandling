#
# Table structure for table 'sys_domain'
#
CREATE TABLE sys_domain (
    tx_pagenotfoundhandling_enable tinyint(3) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_default404Page int(11) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_defaultTemplateFile tinytext,
    tx_pagenotfoundhandling_default403Page int(11) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_default403Header tinyint(4) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_ignoreLanguage tinyint(3) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_forceLanguage int(11) DEFAULT '0' NOT NULL,
    tx_pagenotfoundhandling_languageParam varchar(45) DEFAULT 'L' NOT NULL
);