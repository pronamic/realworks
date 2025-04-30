<?php namespace CustomPost\Reader\Json;

interface JsonSelector
{
	public function select($data, $root);
}
