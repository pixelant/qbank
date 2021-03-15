#
# Extend table 'sys_file'
#
CREATE TABLE sys_file (
	tx_qbank_id int(11) unsigned DEFAULT 0 NOT NULL,
	tx_qbank_file_timestamp int(11) unsigned DEFAULT 0 NOT NULL,
	tx_qbank_metadata_timestamp int(11) unsigned DEFAULT 0 NOT NULL,
	tx_qbank_remote_change tinyint(3) unsigned DEFAULT 0 NOT NULL,

	KEY qbank (tx_qbank_id),
);
