#
# Extend table 'sys_file'
#
CREATE TABLE sys_file (
	tx_qbank_id int(11) unsigned DEFAULT 0 NOT NULL,

	KEY qbank (tx_qbank_id),
);
