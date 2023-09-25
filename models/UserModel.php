<?php
class UserModel extends Model {
	protected static $table_name = "users";
	protected static $update_string = "
		UPDATE users SET
			vk_id=:vk_id,
			state=:state,
			admin=:admin,
			type=:type,
			question_progress=:question_progress,
			allows_mail=:allows_mail,
			gid=:gid,
			journal_login=:journal_login,
			journal_password=:journal_password,
			teacher_id=:teacher_id
		WHERE id=:id";
}
