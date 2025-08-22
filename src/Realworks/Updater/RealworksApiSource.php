<?php namespace Realworks\Updater;

use SimpleXMLElement;
use ZipArchive;
use RuntimeException;
use JoostK\Wordpress\Remote\RemoteInterface;
use CustomPost\Plugin\Updater\ApiSourceInterface;

class RealworksApiSource implements ApiSourceInterface
{
    protected $remote;
    protected $apiClient;
    protected $path;

    public function __construct(RemoteInterface $remote, RealworksApiClient $apiClient)
    {
        $this->remote = $remote;
        $this->apiClient = $apiClient;
    }

    public function setIdentifier($identifier)
    {
        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path . '/archive.zip';
        return $this;
    }

    public function setCredentials($user, $password)
    {
        return $this;
    }

    public function setOffices($offices)
    {
        return $this;
    }

    public function setKoppeling($koppeling)
    {
        return $this;
    }

    public function getArchivePath()
    {
        $data = $this->apiClient->fetchObjects();

        if (empty($data['resultaten'])) {
            $this->createEmptyZip();
            return $this->path;
        }

        $xmlString = $this->convertJsonToXml($data['resultaten']);
        $this->createZipWithXml($xmlString);

        return $this->path;
    }

    protected function createZipWithXml($xmlString)
    {
        $zip = new ZipArchive();
        if ($zip->open($this->path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new RuntimeException("Cannot open <{$this->path}>");
        }
        $zip->addFromString('export.xml', $xmlString);
        $zip->close();
    }

    protected function createEmptyZip()
    {
        $zip = new ZipArchive();
        $zip->open($this->path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->close();
    }

    protected function convertJsonToXml(array $objects)
    {
        $xml = new SimpleXMLElement('<Wonen></Wonen>');
        foreach ($objects as $object) {
            $this->convertObjectToXml($xml, $object);
        }
        return $xml->asXML();
    }

    protected function convertObjectToXml(SimpleXMLElement $xml, array $object)
    {
        $objectNode = $xml->addChild('Object');

        // --- Helper function to safely access array keys ---
        $get = function($array, $key, $default = '') {
            $keys = explode('.', $key);
            foreach ($keys as $k) {
                if (!isset($array[$k])) {
                    return $default;
                }
                $array = $array[$k];
            }
            return $array;
        };

        // --- Map data based on fields.php ---
        $objectNode->addChild('ObjectSystemID', $get($object, 'id'));
        $objectNode->addChild('ObjectTiaraID', $get($object, 'tiaraId'));

        $detailsNode = $objectNode->addChild('ObjectDetails');

        // Address
        $adresNode = $detailsNode->addChild('Adres');
        $nederlandsNode = $adresNode->addChild('Nederlands');
        $nederlandsNode->addChild('Straatnaam', $get($object, 'adres.straat'));
        $nederlandsNode->addChild('Huisnummer', $get($object, 'adres.huisnummer'));
        $nederlandsNode->addChild('HuisnummerToevoeging', $get($object, 'adres.huisnummerToevoeging'));
        $nederlandsNode->addChild('Postcode', $get($object, 'adres.postcode'));
        $nederlandsNode->addChild('Woonplaats', $get($object, 'adres.plaats'));

        // Price
        $koopNode = $detailsNode->addChild('Koop');
        $koopNode->addChild('Koopprijs', $get($object, 'prijs.koopPrijs'));
        $koopNode->addChild('KoopConditie', $get($object, 'prijs.koopConditie'));

        $huurNode = $detailsNode->addChild('Huur');
        $huurNode->addChild('Huurprijs', $get($object, 'prijs.huurPrijs'));
        $huurNode->addChild('HuurConditie', $get($object, 'prijs.huurConditie'));

        // Status
        $statusNode = $detailsNode->addChild('StatusBeschikbaarheid');
        $statusNode->addChild('Status', $get($object, 'status'));

        // Dates
        $detailsNode->addChild('DatumInvoer', $get($object, 'datumAanmelding'));
        $detailsNode->addChild('DatumWijziging', $get($object, 'datumWijziging'));

        // Description
        $detailsNode->addChild('Aanbiedingstekst', $get($object, 'aanbiedingstekst'));

        // --- Wonen Details ---
        $wonenDetailsNode = $objectNode->addChild('Wonen')->addChild('WonenDetails');

        $bestemmingNode = $wonenDetailsNode->addChild('Bestemming');
        $bestemmingNode->addChild('HuidigGebruik', $get($object, 'huidigGebruik'));

        $matenNode = $wonenDetailsNode->addChild('MatenEnLigging');
        $matenNode->addChild('Inhoud', $get($object, 'inhoud'));
        $matenNode->addChild('GebruiksoppervlakteWoonfunctie', $get($object, 'oppervlakten.woonoppervlakte'));
        $matenNode->addChild('PerceelOppervlakte', $get($object, 'perceelOppervlakte'));

        $bouwjaarNode = $wonenDetailsNode->addChild('Bouwjaar');
        $bouwjaarNode->addChild('JaarOmschrijving')->addChild('Jaar', $get($object, 'bouwjaar'));

        // Media
        $mediaLijstNode = $objectNode->addChild('MediaLijst');
        if (!empty($object['media']) && is_array($object['media'])) {
            foreach ($object['media'] as $mediaItem) {
                $mediaNode = $mediaLijstNode->addChild('Media');
                $mediaNode->addChild('Groep', $get($mediaItem, 'categorie'));
                $mediaNode->addChild('URL', $get($mediaItem, 'link'));
            }
        }
    }

    public function downloadArchive()
    {
        // No-op
    }
}
