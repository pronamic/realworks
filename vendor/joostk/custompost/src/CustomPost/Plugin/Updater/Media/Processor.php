<?php namespace CustomPost\Plugin\Updater\Media;

use CustomPost\Contracts\DatabaseInterface;

class Processor
{
	const EVENT = 'custompost_process_media';

	public function process(ProcessingQueue $queue)
	{
		$this->prepare();

		while (($media = $queue->peek()))
		{
			$this->processMedia($media['id'], $media['path']);

			$queue->delete($media);
		}
	}

	public function processMedia($id, $path)
	{
		$data = wp_generate_attachment_metadata($id, $path);

		wp_update_attachment_metadata($id, $data);
	}

	protected function prepare()
	{
		set_time_limit(0);

		require_once ABSPATH.'/wp-admin/includes/media.php';
		require_once ABSPATH.'/wp-admin/includes/image.php';
	}
}
