<?php namespace CustomPost\Plugin;

class AdminNotice
{
	protected $message;

	protected $style;

	public function __construct($message, $style = 'update-nag')
	{
		$this->message = $message;
		$this->style = $style;
	}

	public function styled($style)
	{
		$this->style = $style;

		return $this;
	}

	public function add()
	{
		add_action('all_admin_notices', array($this, 'render'));

		return $this;
	}

	public function render()
	{
		echo '<div class="', $this->style, '">', $this->message, '</div>';
	}

	public static function show()
	{
		$message = call_user_func_array('sprintf', func_get_args());

		$notice = new static($message);

		return $notice->add();
	}
}
