<?php namespace CustomPost\Plugin\Admin\Notices;

class NoticeManager
{
	protected $notices = array();

	protected $dismissedNotices;

	public function __construct($identifier)
	{
		$this->option = '_' . $identifier . '_dismissed_notices';
	}

	public function register(StickyNotice $notice)
	{
		$this->notices[$notice->name()] = $notice;

		return $this;
	}

	public function getNotice($name)
	{
		return isset($this->notices[$name]) ? $this->notices[$name] : null;
	}

	public function process()
	{
		$this->dismissedNotices = get_option($this->option, array());

		$this->processDismissals();

		$this->showNotices();
	}

	protected function processDismissals()
	{
		if (isset($_GET['dismiss_notice']) and $notice = $this->getNotice($_GET['dismiss_notice']))
		{
			$this->dismissedNotices[$notice->name()] = true;

			update_option($this->option, $this->dismissedNotices);
		}
	}

	protected function showNotices()
	{
		foreach ($this->notices as $notice)
		{
			if ( ! $this->isDismissed($notice))
			{
				$notice->show();
			}
		}
	}

	protected function isDismissed(StickyNotice $notice)
	{
		return isset($this->dismissedNotices[$notice->name()]) and $this->dismissedNotices[$notice->name()];
	}
}
