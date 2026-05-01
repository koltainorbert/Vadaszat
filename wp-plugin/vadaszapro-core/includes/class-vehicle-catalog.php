<?php
/**
 * Használtautó.hu alapú jármű-katalógus adatok.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Vehicle_Catalog {

    public static function get_dataset_version(): string {
        return 'hasznaltauto-2026-05-01';
    }

    public static function get_categories(): array {
        return [
            [ 'name' => 'Kisautó',        'slug' => 'kisauto' ],
            [ 'name' => 'Városi autó',    'slug' => 'varosi-auto' ],
            [ 'name' => 'Családi autó',   'slug' => 'csaladi-auto' ],
            [ 'name' => 'Terepjáró',      'slug' => 'terepjaro' ],
            [ 'name' => 'Kishaszonjármű', 'slug' => 'kishaszonjarmu' ],
        ];
    }

    public static function get_brands(): array {
        return [
            'ABARTH','AIXAM','ALFA ROMEO','ALPINE','ARCTIC CAT','ARO','ASIA','ASTON MARTIN','AUDI','AUSTIN',
            'AVIA','BAJAJ','BARKAS','BEDFORD','BENTLEY','BMC','BMW','BOMBARDIER','BORGWARD','BYD',
            'CADILLAC','CENNTRO','CHERY','CHEVROLET','CHRYSLER','CITROEN','CUPRA','DACIA','DAEWOO','DAF',
            'DAIHATSU','DODGE','DONGFENG (DFSK)','DS','EVEASY','FARIZON','FERRARI','FIAT','FISKER','FORD',
            'FOTON','GAZ','GEELY','GENESIS','GMC','GREAT WALL','HONDA','HUMMER','HYUNDAI','INEOS',
            'INFINITI','ISUZU','IVECO','JAECOO','JAGUAR','JEEP','KAWASAKI','KGM (SSANGYONG)','KIA','KTM',
            'KYMCO','LADA','LANCIA','LAND ROVER','LDV','LEAPMOTOR','LEVC','LEXUS','LIGIER','LINCOLN',
            'LOTUS','MAHINDRA','MAN','MARUTI','MASERATI','MAXUS','MAZDA','MEGA','MERCEDES-AMG','MERCEDES-BENZ',
            'MERCEDES-MAYBACH','MG','MINI','MITSUBISHI','MOSZKVICS','MULTICAR','NIO','NISSAN','OLTCIT','OMODA',
            'OPEL','PEUGEOT','PIAGGIO','POLARIS','POLONEZ','POLSKI FIAT','PORSCHE','PROTON','RENAULT','ROLLS-ROYCE',
            'ROVER','SAAB','SEAT','SHUANGHUAN','SKODA','SKYWELL','SMART','SSANGYONG','STEYR','SUBARU',
            'SUZUKI','TALBOT','TATA','TESLA','TOYOTA','TRABANT','TRIUMPH','UAZ','VAUXHALL','VOLGA',
            'VOLKSWAGEN','VOLVO','VOYAH','WARTBURG','XPENG','YAMAHA','YUGO','ZASTAVA','ZAZ',
        ];
    }

    public static function get_body_type_options(): array {
        return [
            'hatchback'           => 'ferdehátú',
            'sedan'               => 'sedan',
            'wagon'               => 'kombi',
            'cabrio'              => 'cabrio',
            'mpv'                 => 'egyterű',
            'coupe'               => 'coupe',
            'crossover'           => 'városi terepjáró (crossover)',
            'closed'              => 'zárt',
            'double_cab_chassis'  => 'duplakabinos alváz',
            'pickup'              => 'pickup',
            'minibus'             => 'kisbusz',
            'single_cab_chassis'  => 'alváz szimpla kabin',
        ];
    }
}
