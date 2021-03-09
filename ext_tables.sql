#
# Table structure for table 'sys_file'
#
CREATE TABLE sys_file (
	# additional fields for qbank
	is_qbank_media int(11) DEFAULT '0' NOT NULL,
	qbank_metadata_updated int(11) DEFAULT '0' NOT NULL,
	qbank_media_updated int(11) DEFAULT '0' NOT NULL,
	qbank_sync_updated int(11) DEFAULT '0' NOT NULL,
	be_user_id int(11) DEFAULT '0' NOT NULL,
);