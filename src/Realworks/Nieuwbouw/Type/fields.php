<?php

use CustomPost\Fields\Field;
use CustomPost\Fields\Subtype;
use Realworks\Common\PrimaryKeySelector;

return array(
	Field::int('id')->select(new PrimaryKeySelector)->primary()->unique(),
	Field::string('user'),
	Field::string('NVMVestigingNR')->select('./NVMVestigingNR'),
	Field::string('objectAfdeling')->select('./ObjectAfdeling'),
	Field::int('objectTiaraID')->select('./ObjectTiaraID'),
	Field::int('objectSystemID')->select('./ObjectSystemID'),
	Field::string('objectCode')->select('./ObjectCode'),
	Field::string('naam')->select('./BouwTypeDetails/Naam'),
	Field::date('datumInvoer')->select('./BouwTypeDetails/DatumInvoer')->parseFormat('Y-m-d'),
	Field::date('datumWijziging')->select('./BouwTypeDetails/DatumWijziging')->parseFormat('Y-m-d'),
	Field::int('aantalEenheden')->select('./BouwTypeDetails/AantalEenheden'),
	Field::int('aantalVrijeEenheden')->select('./BouwTypeDetails/AantalVrijeEenheden'),
	Field::date('datumStartBouw')->select('./BouwTypeDetails/DatumStartBouw')->parseFormat('Y-m-d'),
	Field::string('omschrijvingStartBouw')->select('./BouwTypeDetails/OmschrijvingStartBouw'),
	Field::date('datumOpleveringVanaf')->select('./BouwTypeDetails/DatumOpleveringVanaf')->parseFormat('Y-m-d'),
	Field::string('omschrijvingOpleveringVanaf')->select('./BouwTypeDetails/OmschrijvingOpleveringVanaf'),
	Field::text('inschrijfvoorwaarden')->select('./BouwTypeDetails/Inschrijfvoorwaarden'),
	Field::string('wachttijd')->select('./BouwTypeDetails/Wachttijd'),
	Field::int('inhoudVan')->select('./BouwTypeDetails/Maten/Inhoud/Van')->formatter('volume'),
	Field::int('inhoudTotEnMet')->select('./BouwTypeDetails/Maten/Inhoud/TotEnMet')->formatter('volume'),
	Field::int('woonoppervlakteVan')->select('./BouwTypeDetails/Maten/Woonoppervlakte/Van')->formatter('area'),
	Field::int('woonoppervlakteTotEnMet')->select('./BouwTypeDetails/Maten/Woonoppervlakte/TotEnMet')->formatter('area'),
	Field::int('perceeloppervlakteVan')->select('./BouwTypeDetails/Maten/Perceeloppervlakte/Van')->formatter('area'),
	Field::int('perceeloppervlakteTotEnMet')->select('./BouwTypeDetails/Maten/Perceeloppervlakte/TotEnMet')->formatter('area'),
	Field::int('woonkameroppervlakteVan')->select('./BouwTypeDetails/Maten/Woonkameroppervlakte/Van')->formatter('area'),
	Field::int('woonkameroppervlakteTotEnMet')->select('./BouwTypeDetails/Maten/Woonkameroppervlakte/TotEnMet')->formatter('area'),
	Field::text('aanbiedingstekst')->select('./BouwTypeDetails/Aanbiedingstekst'),
	Field::int('koopAanneemsomVan')->select('./BouwTypeDetails/FinancieleGegevens/KoopAanneemsom/Van')->formatter('money'),
	Field::int('koopAanneemsomTotEnMet')->select('./BouwTypeDetails/FinancieleGegevens/KoopAanneemsom/TotEnMet')->formatter('money'),
	Field::int('huurprijsVan')->select('./BouwTypeDetails/FinancieleGegevens/Huurprijs/Van')->formatter('money'),
	Field::int('huurprijsTotEnMet')->select('./BouwTypeDetails/FinancieleGegevens/Huurprijs/TotEnMet')->formatter('money'),
	Subtype::collection('medialijst', './MediaLijst/Media', array(
		Field::string('groep')->select('./Groep'),
		Field::string('url')->select('./URL')->length(1000),
		Field::string('omschrijving')->select('./MediaOmschrijving'),
		Field::date('gewijzigd')->select('./MediaUpdate')->parseFormat('Y-m-d\TH:i:s'),
	)),
);
