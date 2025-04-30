<?php namespace CustomPost\Plugin\Updater\Media;

use Exception;
use RuntimeException;
use CustomPost\Database\Post;
use CustomPost\Plugin\Updater\Canceller;
use CustomPost\Plugin\Updater\Logger\LoggerInterface;

class Updater
{
	const PROCESS = 'process_media_event';

	protected $model;

	protected $fetcher;

	protected $logger;

	protected $canceller;

	protected $queue;

	protected $basePath;

	protected $updated;

	protected $total;

	protected $counter;

	protected $counts;

	protected $processed;

	protected $thumbnail;

	protected $shouldPop;

	public function __construct(Post $model, Fetcher $fetcher, LoggerInterface $logger, Canceller $canceller, ProcessingQueue $queue, $basePath)
	{
		$this->model = $model;
		$this->fetcher = $fetcher;
		$this->logger = $logger;
		$this->canceller = $canceller;
		$this->queue = $queue;
		$this->basePath = $basePath;
	}

	public function reset()
	{
		$this->updated = 0;
		$this->counter = 1;
		$this->counts = array();
		$this->processed = array();
		$this->thumbnail = null;
		$this->shouldPop = true;
	}

	protected function pop()
	{
		if ($this->shouldPop) $this->logger->pop();
		else $this->shouldPop = true;

		return $this->logger;
	}

	public function update(array $items)
	{
		if ( ! $this->ensureDirectory($this->basePath)) return;

		$this->reset();
		$this->updateItems($items);

		if ($this->updated > 0)
		{
			$this->logger->info("{$this->updated} media items gedownload.");
		}

		if ($this->thumbnail) $this->setThumbnail($this->thumbnail);

		$this->deleteUnprocessed();
	}

	protected function updateItems(array $items)
	{
		$this->logger->info('Bijwerken van media items.');

		$this->total = count($items);

		foreach ($items as $media)
		{
			try
			{
				$attachment = $this->processMedia($media);

				$this->processed($media, $attachment);
			}
			catch (Exception $e)
			{
				error_log((string) $e);

				$this->logger->warning($e->getMessage());
				$this->shouldPop = false;
			}

			$this->canceller->check();

			$this->counter++;

			wp_cache_flush();
			gc_collect_cycles();
		}

		$this->pop();
	}

	protected function ensureDirectory($path)
	{
		if (wp_mkdir_p($path))
		{
			return true;
		}
		else
		{
			$this->logger->error("Mediamap [{$path}] kon niet worden aangemaakt.");

			return false;
		}
	}

	protected function processed(Media $media, $attachment)
	{
		if ($media->isThumbnail())
		{
			$this->thumbnail = $attachment;
		}

		// If no thumbnail has been set, remember an image as potential thumbnail
		elseif ($this->thumbnail === null and $media->isImage())
		{
			$this->thumbnail = $attachment;
		}

		$this->processed[] = $this->getMediaId($media);
	}

	protected function processMedia(Media $media)
	{
		$this->trackCounts($media);

		if ($attachment = $this->getAttachment($media))
		{
			return $this->processExistingMedia($media, $attachment);
		}
		else
		{
			$this->pop()->info("{$this->counter} / {$this->total}: Nieuw media item downloaden: {$this->getMediaDescription($media)}");

			return $this->processNewMedia($media);
		}
	}

	protected function trackCounts(Media $media)
	{
		list($type, $group) = array($media->getType(), $media->getGroup());

		if ( ! isset($this->counts[$type][$group]))
		{
			$this->counts[$type][$group] = 1;
		}
		else
		{
			$this->counts[$type][$group]++;
		}
	}

	protected function processExistingMedia(Media $media, $attachment)
	{
		if ($media->getModified() == get_post_meta($attachment->ID, '_media_modified', true))
		{
			$this->updateAttachment($attachment, $media);

			return $attachment;
		}
		else
		{
			$this->pop()->info("{$this->counter} / {$this->total}: Bijgewerkt media item opnieuw downloaden: {$this->getMediaDescription($media)}");

			// Delete the outdated attachment
			wp_delete_attachment($attachment->ID, true);

			return $this->processNewMedia($media);
		}
	}

	protected function updateAttachment($attachment, Media $media)
	{
		update_post_meta($attachment->ID, 'media_type', $media->getType());
		update_post_meta($attachment->ID, 'media_group', $media->getGroup());
		update_post_meta($attachment->ID, 'media_description', $media->getDescription());

		foreach ($media->getMeta() as $key => $value)
		{
			update_post_meta($attachment->ID, $key, $value);
		}

		update_post_meta($attachment->ID, '_media_id', $this->getMediaId($media));
		update_post_meta($attachment->ID, '_media_position', $this->getMediaPosition($media));
		update_post_meta($attachment->ID, '_media_modified', $media->getModified());

		wp_update_post(array(
			'ID' => $attachment->ID,
			'post_title' => $this->getMediaTitle($media),
			'post_excerpt' => $this->getMediaDescription($media),
			'post_content' => $media->getDescription(),
		));
	}

	protected function processNewMedia(Media $media)
	{
		$path = $this->downloadMedia($media);
		$attachment = $this->createAttachment($media, $path);

		$this->updateAttachment($attachment, $media);

		$this->updated++;

		return $attachment;
	}

	protected function downloadMedia(Media $media)
	{
		$path = $this->mediaPath($media);

		try
		{
			$this->fetcher->fetch($media, $path);
		}
		catch (Exception $e)
		{
			$id = $this->getMediaId($media);

			throw new RuntimeException("Media item [{$id}] kon niet worden verwerkt: {$e->getMessage()}", 0, $e);
		}

		return $path;
	}

	protected function mediaPath(Media $media)
	{
		$directory = "{$this->basePath}/{$this->model->primary()}";

		if ( ! $this->ensureDirectory($directory))
		{
			throw new RuntimeException("Mediamap [{$directory}] kon niet worden aangemaakt.");
		}

		$id = preg_replace('/[^\w\-._]/', '', str_replace(array('%20', '+', ' '), '-', $this->getMediaId($media)));

		return "{$directory}/{$id}{$media->getExtension()}";
	}

	protected function createAttachment(Media $media, $path)
	{
		$filetype = wp_check_filetype($path);
		$attachment = array(
			'post_mime_type' => $filetype['type'],
			'post_title' => $this->getMediaTitle($media),
			'post_status' => 'inherit',
		);
		$id = wp_insert_attachment($attachment, $path, $this->model->getPrimaryKey());

		$this->generateMetadata($id, $path);

		return get_post($id);
	}

	protected function generateMetadata($id, $path)
	{
		$this->queue->push($id, $path);
	}

	protected function setThumbnail($thumbnail)
	{
		// Some plugins rely on this
		$GLOBALS['post'] = $thumbnail;
		setup_postdata($thumbnail);

		set_post_thumbnail($this->model->getPrimaryKey(), $thumbnail->ID);
	}

	protected function getMediaTitle(Media $media)
	{
		return $this->model->getWordpressPost()->post_title . ' – ' . $this->getMediaDescription($media);
	}

	protected function getMediaDescription(Media $media)
	{
		$description = ucfirst(strtolower($media->getGroup()));

		if (($count = $this->counts[$media->getType()][$media->getGroup()]) > 1)
		{
			$description .= ' ' . $count;
		}

		return $description;
	}

	protected function getMediaId(Media $media)
	{
		return $media->getId() ?: $this->counter;
	}

	protected function getMediaPosition(Media $media)
	{
		return $media->getPosition() ?: $this->counter;
	}

	protected function deleteUnprocessed()
	{
		$attachments = get_children(array(
			'post_type' => 'attachment',
			'post_parent' => $this->model->getPrimaryKey(),
			'numberposts' => -1,
			'meta_query' => array(
				array(
					'key' => '_media_id',
					'compare' => 'NOT IN',
					'value' => $this->processed,
				),
			),
		));

		foreach ($attachments as $attachment)
		{
			wp_delete_attachment($attachment->ID, true);
		}
	}

	protected function getAttachment($media)
	{
		$attachments = get_children(array(
			'post_type' => 'attachment',
			'post_parent' => $this->model->getPrimaryKey(),
			'numberposts' => 1,
			'meta_query' => array(
				array(
					'key' => '_media_id',
					'value' => $this->getMediaId($media),
				),
			),
		));

		return reset($attachments) ?: null;
	}
}
