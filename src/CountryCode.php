<?php

namespace Plastonick\Euros;

enum CountryCode: string
{
    case ARG = 'ARG';
    case AUS = 'AUS';
    case AUT = 'AUT';
    case BEL = 'BEL';
    case BRA = 'BRA';
    case CAN = 'CAN';
    case CMR = 'CMR';
    case CRC = 'CRC';
    case CRO = 'CRO';
    case CZE = 'CZE';
    case DEN = 'DEN';
    case ECU = 'ECU';
    case ENG = 'ENG';
    case ESP = 'ESP';
    case FIN = 'FIN';
    case FRA = 'FRA';
    case GER = 'GER';
    case GHA = 'GHA';
    case HUN = 'HUN';
    case IRN = 'IRN';
    case ITA = 'ITA';
    case JPN = 'JPN';
    case KOR = 'KOR';
    case KSA = 'KSA';
    case MAR = 'MAR';
    case MEX = 'MEX';
    case MKD = 'MKD';
    case NED = 'NED';
    case POL = 'POL';
    case POR = 'POR';
    case QAT = 'QAT';
    case RUS = 'RUS';
    case SCO = 'SCO';
    case SEN = 'SEN';
    case SRB = 'SRB';
    case SUI = 'SUI';
    case SVK = 'SVK';
    case SWE = 'SWE';
    case TUN = 'TUN';
    case TUR = 'TUR';
    case UKR = 'UKR';
    case URU = 'URU';
    case USA = 'USA';
    case WAL = 'WAL';

    public function getFlagCode(): string
    {
        return match ($this) {
            self::ARG => 'ar',
            self::AUS => 'au',
            self::AUT => 'at',
            self::BEL => 'be',
            self::BRA => 'br',
            self::CAN => 'ca',
            self::CMR => 'cm',
            self::CRC => 'cr',
            self::CRO => 'hr',
            self::CZE => 'cz',
            self::DEN => 'dk',
            self::ECU => 'ec',
            self::ENG => 'england',
            self::ESP => 'es',
            self::FIN => 'fi',
            self::FRA => 'fr',
            self::GER => 'de',
            self::GHA => 'gh',
            self::HUN => 'hu',
            self::IRN => 'ir',
            self::ITA => 'it',
            self::JPN => 'jp',
            self::KOR => 'kr',
            self::KSA => 'sa',
            self::MAR => 'ma',
            self::MEX => 'mx',
            self::MKD => 'mk',
            self::NED => 'nl',
            self::POL => 'pl',
            self::POR => 'pt',
            self::QAT => 'qa',
            self::RUS => 'ru',
            self::SCO => 'scotland',
            self::SEN => 'sn',
            self::SRB => 'rs',
            self::SUI => 'ch',
            self::SVK => 'sk',
            self::SWE => 'se',
            self::TUN => 'tn',
            self::TUR => 'tr',
            self::UKR => 'ua',
            self::URU => 'uy',
            self::USA => 'us',
            self::WAL => 'wales',
        };
    }
}
