<?php namespace Realworks\Admin;

class Addons
{
	public function get()
	{
		return array(
			'eigen-woning-website' => $this->getEigenWoningWebsite(),
			'buurten' => $this->getBuurten(),
			'xml-generator' => $this->getXmlGenerator(),
			'diorama' => $this->getDiorama(),
			'verzoek-indienen' => $this->getVerzoekIndienen(),
		);
	}

	protected function getEigenWoningWebsite()
	{
		return array(
			'title' => 'Eigen Woning Website',
			'description' => 'Een unieke website voor iedere woning!',
			'image' => 'https://tussendoor.nl/externe-afbeeldingen/plugins/addons/makelaar/eigenwoningwebsite@2x.jpg',
			'link' => 'https://tussendoor.nl/wordpress-plugins/makelaar-addon-eigen-woning-website',
			'page' => admin_url('admin.php?page=property-extension-settings'),
			'available' => class_exists('Tussendoor\\PropertyWebsite\\Plugin'),
		);
	}

	protected function getBuurten()
	{
		return array(
			'title' => 'Buurten',
			'description' => 'Stad, wijk, buurt en straatnaam. Alle filters zijn mogelijk!',
			'image' => 'https://tussendoor.nl/externe-afbeeldingen/plugins/addons/makelaar/buurten@2x.jpg',
			'link' => 'https://tussendoor.nl/wordpress-plugins/makelaar-addon-buurten-wijken',
			'page' => admin_url('admin.php?page=TBA-settings'),
			'available' => class_exists('Tussendoor\\Neighborhood\\Plugin'),
		);
	}

	protected function getXmlGenerator()
	{
		return array(
			'title' => 'XML Generator',
			'description' => 'Automatisch genereren van XML bestanden om handmatig wijzigingen door te voeren.',
			'image' => 'https://tussendoor.nl/externe-afbeeldingen/plugins/addons/makelaar/xmlgenerator@2x.jpg',
			'link' => 'https://tussendoor.nl/wordpress-plugins/xml-generator-plugin-addon',
			'page' => admin_url('admin.php?page=tsd_xml_generator'),
			'available' => class_exists('tsd_xml_generator'),
		);
	}

	protected function getDiorama()
	{
		return array(
			'title' => 'Diorama',
			'description' => 'Laat anderen unieke informatie delen over jouw woning!',
			'image' => 'https://tussendoor.nl/externe-afbeeldingen/plugins/addons/makelaar/kijkmijnhuis@2x.jpg',
			'link' => 'https://tussendoor.nl/wordpress-plugins/makelaar-addon-diorama-helpmee',
			'page' => admin_url('admin.php?page=helpmee-settings'),
			'available' => class_exists('Tussendoor\\Helpmee\\Plugin'),
		);
	}

	protected function getVerzoekIndienen()
	{
		return array(
			'title' => 'Addon Verzoeken?',
			'description' => 'Laat het ons weten als je een addon verzoek hebt!',
			'image' => 'https://tussendoor.nl/externe-afbeeldingen/plugins/addons/makelaar/tips@2x.jpg',
			'link' => 'https://tussendoor.nl/contact',
			'page' => '',
			'available' => false,
		);
	}
}
