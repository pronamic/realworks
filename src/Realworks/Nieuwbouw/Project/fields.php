<?php

use CustomPost\Fields\Field;
use CustomPost\Fields\Subtype;
use Realworks\Common\PrimaryKeySelector;

return array(
	Field::int('id')->select(new PrimaryKeySelector)->primary()->unique(),
	Field::string('user'),
	Field::string('NVMVestigingNR')->select('./NVMVestigingNR'),
	Field::string('objectCompany')->select('./ObjectCompany'),
	Field::string('objectAfdeling')->select('./ObjectAfdeling'),
	Field::int('objectTiaraID')->select('./ObjectTiaraID'),
	Field::int('objectSystemID')->select('./ObjectSystemID'),
	Field::string('objectCode')->select('./ObjectCode'),
	Field::string('projectnaam')->select('./ProjectDetails/Projectnaam'),
	Field::string('adres')->select('./ProjectDetails/Adres/Postcode'),
	Field::string('postcode')->select('./ProjectDetails/Adres/Postcode'),
	Field::string('plaats')->select('./ProjectDetails/Adres/Woonplaats')->formatter('Realworks\Common\Formatters\CityFormatter')->hasVirtualDependency(true),
	Field::date('datumInvoer')->select('./ProjectDetails/DatumInvoer')->parseFormat('Y-m-d'),
	Field::date('datumWijziging')->select('./ProjectDetails/DatumWijziging')->parseFormat('Y-m-d'),
	Field::date('datumStartBouw')->select('./ProjectDetails/DatumStartBouw')->parseFormat('Y-m-d'),
	Field::string('omschrijvingStartBouw')->select('./ProjectDetails/OmschrijvingStartBouw'),
	Field::date('datumOpleveringVanaf')->select('./ProjectDetails/DatumOpleveringVanaf')->parseFormat('Y-m-d'),
	Field::string('omschrijvingOpleveringVanaf')->select('./ProjectDetails/OmschrijvingOpleveringVanaf'),
	Field::int('inhoudVan')->select('./ProjectDetails/Maten/Inhoud/Van')->formatter('volume'),
	Field::int('inhoudTotEnMet')->select('./ProjectDetails/Maten/Inhoud/TotEnMet')->formatter('volume'),
	Field::int('woonoppervlakteVan')->select('./ProjectDetails/Maten/Woonoppervlakte/Van')->formatter('area'),
	Field::int('woonoppervlakteTotEnMet')->select('./ProjectDetails/Maten/Woonoppervlakte/TotEnMet')->formatter('area'),
	Field::int('perceeloppervlakteVan')->select('./ProjectDetails/Maten/Perceeloppervlakte/Van')->formatter('area'),
	Field::int('perceeloppervlakteTotEnMet')->select('./ProjectDetails/Maten/Perceeloppervlakte/TotEnMet')->formatter('area'),
	Field::text('aanbiedingstekst')->select('./ProjectDetails/Presentatie/Aanbiedingstekst'),
	Field::string('website')->select('./ProjectDetails/Presentatie/Website'),
	Field::string('omgeving')->select('./ProjectDetails/Presentatie/Omgeving')->length(4000),
	Field::int('koopAanneemsomVan')->select('./ProjectDetails/FinancieleGegevens/KoopAanneemsom/Van')->formatter('money'),
	Field::int('koopAanneemsomTotEnMet')->select('./ProjectDetails/FinancieleGegevens/KoopAanneemsom/TotEnMet')->formatter('money'),
	Field::int('huurprijsVan')->select('./ProjectDetails/FinancieleGegevens/Huurprijs/Van')->formatter('money'),
	Field::int('huurprijsTotEnMet')->select('./ProjectDetails/FinancieleGegevens/Huurprijs/TotEnMet')->formatter('money'),
	Subtype::collection('medialijst', './MediaLijst/Media', array(
		Field::string('groep')->select('./Groep'),
		Field::string('url')->select('./URL')->length(1000),
		Field::string('omschrijving')->select('./MediaOmschrijving'),
		Field::date('gewijzigd')->select('./MediaUpdate')->parseFormat('Y-m-d\TH:i:s'),
	)),
);
