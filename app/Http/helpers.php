<?php
if (!function_exists('get_ip_address')) {
    /**
     * get ip address behind cloudflare
     */
    function get_ip_address()
    {
        if (!isset($_SERVER["REMOTE_ADDR"])) {
            $ip_address = "127.0.0.1";
        } // running in CLI mode
        else {
            $ip_address = $_SERVER["REMOTE_ADDR"];
        }

        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip_array = explode(",", getenv('HTTP_X_FORWARDED_FOR'));
            $ip_address = array_shift($ip_array);
        }

        return $ip_address;
    }
}

if (!function_exists('satoshitize')) {
    /**
     * Converts a value to a standard crypto-currency format
     * @param $value
     * @param bool $format -- formats a number (added command as thousand separator but retains precision, number of decimals)
     * @param bool $ru0 (remove useless zeroes)
     * @return string
     */
    function satoshitize($value, $format=true, $ru0=false)
    {
        $satoshitized = sprintf("%.8f", $value);
        if ($ru0) {
            $float = $satoshitized;
            $integer = sprintf('%d', $satoshitized);
            if ($float == $integer) {
                $satoshitized = $integer;
            } else {
                $satoshitized = rtrim($float, '0');
            }
        }
        //if($ru0) $satoshitized += 0;
        if (!$format) {
            return $satoshitized;
        }
        $broken_number = explode(".", $satoshitized);
        #print_r($broken_number);
        $formatted = number_format($broken_number[0]);
        #echo "Formatted whole number: ".$formatted;
        if (!empty($broken_number[1])) {
            $formatted .= ".".$broken_number[1];
        }
        return ($value < 0 && $formatted > 0) ? "-".$formatted : $formatted;
    }
}

if (!function_exists('privatize')) {
    /**
     * Truncate string on the middle
     *
     * @param $text     string
     * @param $maxChars integer
     * @param $mask     string
     *
     * @return string
     */
    function privatize($text, $maxChars = 8, $mask = "***")
    {
        $textLength = strlen($text);

        return substr_replace($text, $mask, $maxChars / 2, $textLength - $maxChars);
    }
}

if (!function_exists('currency')) {
    /**
     * Format currency with auto converting to floating points
     *
     * @param $number
     * @param int $decimal
     *
     * @return string
     */
    function currency($number, $decimal = 8)
    {
        return number_format(round($number, 8), $decimal, '.', '');
    }
}

if (!function_exists('currency_format')) {
    /**
     * Format currency with auto converting to floating points
     *
     * @param $number
     * @param int $decimal
     *
     * @return string
     */
    function currency_format($number, $decimal = 8)
    {
        return number_format(round($number, 8), $decimal, '.', ',');
    }
}

if (!function_exists('getCoins')) {
    /**
     * @return array
     */
    function getCoins()
    {
        // \Illuminate\Support\Facades\Cache::forget('market-coinselect');
        return \Illuminate\Support\Facades\Cache::remember('market-coinselect', 60, function () {
            $coins = [];
            $coinsObj = \Buzzex\Models\ExchangeItem::where('deleted', '=', 0)
                        ->where('type', '<>', 4)
                        ->orderBy('symbol', 'asc')
                        ->get();
            if ($coinsObj) {
                foreach ($coinsObj as $coin) {
                    $coins[$coin->symbol] = $coin->name;
                }
            }

            return empty($coins) ? ['BZX' => 'BuzzexCoin'] : $coins;
        });
    }
}

if (!function_exists('getCoinItems')) {
    /**
     * @return array
     */
    function getCoinItems($exclude=[])
    {
        // \Illuminate\Support\Facades\Cache::forget('market-coinselect');
        //return \Illuminate\Support\Facades\Cache::remember('market-coinitemsselect-', 1, function () {
            $coins = [];
            $markets = \Buzzex\Models\ExchangeMarket::select("item_id")->get()->toArray();
            $coinsObj = \Buzzex\Models\ExchangeItem::where('deleted', '=', 0)
                            ->where('type', '<>', 4)
                            ->whereNotIn('item_id', array_pluck($markets, 'item_id'))
                            ->orderBy('symbol');
            if($exclude){
                $coinsObj = $coinsObj->whereNotIn('item_id', $exclude);
            }
            $coinsObj = $coinsObj->get();
            if ($coinsObj) {
                foreach ($coinsObj as $coin) {
                    
                    $coins[$coin->item_id] = $coin->symbol;
                }
            }

            return empty($coins) ? ['BZX' => 'BuzzexCoin'] : $coins;
        //});
    }
}

if (!function_exists('getPairs')) {
    /**
     * @return array
     */
    function getPairs()
    {
        // \Illuminate\Support\Facades\Cache::forget('market-pairselect');
        return \Illuminate\Support\Facades\Cache::remember('market-pairselect', 60, function () {
            $pairs = [];
            $pairsObj = \Buzzex\Models\ExchangePairStat::selectRaw('exchange_pairs_stats.pair_id, exchange_pairs_stats.pair_text')
                        ->join('exchange_pairs', 'exchange_pairs_stats.pair_id', '=', 'exchange_pairs.pair_id')
                        ->where('exchange_pairs.deleted', '=', 0)
                        ->orderBy('pair_text', 'asc')
                        ->get();
            if ($pairsObj) {
                foreach ($pairsObj as $pair) {
                    preg_match('/_ACT[0-9]/', trim($pair->pair_text), $match);
                    if (!empty($match)) {
                        continue;
                    }
                    $pairs[$pair->pair_id] = str_replace('_', '/', $pair->pair_text);
                }
            }
            return $pairs;
        });
    }
}
if (!function_exists('getBases')) {
    /**
     * @return array
     */
    function getBases()
    {
        // \Illuminate\Support\Facades\Cache::forget('market-bases');
        return \Illuminate\Support\Facades\Cache::remember('market-bases', 60, function () {
            $coins = [];
            $markets = \Buzzex\Models\ExchangeMarket::orderBy('order')->get();
            if ($markets) {
                foreach ($markets as $market) {
                    $coins[] = $market->exchangeItem->symbol;
                }
            }

            return empty($coins) ? ['ETC'] : $coins;
        });
    }
}


if (!function_exists('getCountryOptions')) {
    /**
     * @return array
     */
    function getCountryOptions($code = "")
    {
        $code = strtoupper($code);
        $nations = [
            'USA' => 'United States',
            'AFG' => 'Afghanistan',
            'ALB' => 'Albania',
            'DZA' => 'Algeria',
            'AND' => 'Andorra',
            'AGO' => 'Angola',
            'AIA' => 'Anguilla',
            'ATG' => 'Antigua and Barbuda',
            'ARG' => 'Argentina',
            'ARM' => 'Armenia',
            'AUS' => 'Australia',
            'AUT' => 'Austria',
            'AZE' => 'Azerbaijan',
            'BHS' => 'Bahamas',
            'BHR' => 'Bahrain',
            'BGD' => 'Bangladesh',
            'BRB' => 'Barbados',
            'BLR' => 'Belarus',
            'BEL' => 'Belgium',
            'BLZ' => 'Belize',
            'BEN' => 'Benin',
            'BMU' => 'Bermuda',
            'BTN' => 'Bhutan',
            'BOL' => 'Bolivia',
            'BIH' => 'Bosnia and Herzegovina',
            'BWA' => 'Botswana',
            'BRA' => 'Brazil',
            'BRN' => 'Brunei Darussalam',
            'BGR' => 'Bulgaria',
            'BFA' => 'Burkina Faso',
            'BDI' => 'Burundi',
            'KHM' => 'Cambodia',
            'CMR' => 'Cameroon',
            'CAN' => 'Canada',
            'CPV' => 'Cape Verde',
            'CYM' => 'Cayman Islands',
            'TCD' => 'Chad',
            'CHL' => 'Chile',
            'CHN' => 'China',
            'COL' => 'Colombia',
            'COM' => 'Comoros',
            'COD' => 'Congo The Democratic Republic of',
            'CRI' => 'Costa Rica',
            'CIV' => 'Cote d\'Ivoire',
            'HRV' => 'Croatia',
            'CUB' => 'Cuba',
            'CYP' => 'Cyprus',
            'CZE' => 'Czech Republic',
            'DNK' => 'Denmark',
            'DJI' => 'Djibouti',
            'DMA' => 'Dominica',
            'DOM' => 'Dominican Republic',
            'ECU' => 'Ecuador',
            'EGY' => 'Egypt',
            'SLV' => 'El Salvador',
            'GNQ' => 'Equatorial Guinea',
            'EST' => 'Estonia',
            'ETH' => 'Ethiopia',
            'FJI' => 'Fiji',
            'FIN' => 'Finland',
            'FRA' => 'France',
            'GAB' => 'Gabon',
            'GMB' => 'Gambia',
            'GEO' => 'Georgia',
            'DEU' => 'Germany',
            'GHA' => 'Ghana',
            'GIB' => 'Gibraltar',
            'GRC' => 'Greece',
            'GRD' => 'Grenada',
            'GUM' => 'Guam',
            'GTM' => 'Guatemala',
            'GGY' => 'Guernsey',
            'GIN' => 'Guinea',
            'GNB' => 'Guinea Bissau',
            'GUY' => 'Guyana',
            'HTI' => 'Haiti',
            'HND' => 'Honduras',
            'HKG' => 'Hong Kong',
            'HUN' => 'Hungary',
            'ISL' => 'Iceland',
            'IND' => 'India',
            'IDN' => 'Indonesia',
            'IRN' => 'Iran Islamic Republic of',
            'IRQ' => 'Iraq',
            'IRL' => 'Ireland',
            'IMN' => 'Isle of Man',
            'ISR' => 'Israel',
            'ITA' => 'Italy',
            'JAM' => 'Jamaica',
            'JPN' => 'Japan',
            'JEY' => 'Jersey',
            'JOR' => 'Jordan',
            'KAZ' => 'Kazakhstan',
            'KEN' => 'Kenya',
            'KOR' => 'Korea (South)',
            'XKX' => 'Kosovo',
            'KWT' => 'Kuwait',
            'KGZ' => 'Kyrgyzstan',
            'LAO' => 'Lao Peoples Democratic Republic of',
            'LVA' => 'Latvia',
            'LBN' => 'Lebanon',
            'LSO' => 'Lesotho',
            'LBR' => 'Liberia',
            'LBY' => 'Libyan Arab Jamahiriya',
            'LIE' => 'Liechtenstein',
            'LTU' => 'Lithuania',
            'LUX' => 'Luxembourg',
            'MAC' => 'Macao',
            'MKD' => 'Macedonia The former Yugoslav Republic of',
            'MDG' => 'Madagascar',
            'MWI' => 'Malawi',
            'MYS' => 'Malaysia',
            'MDV' => 'Maldives',
            'MLI' => 'Mali',
            'MLT' => 'Malta',
            'MHL' => 'Marshall Islands',
            'MRT' => 'Mauritania',
            'MUS' => 'Mauritius',
            'MEX' => 'Mexico',
            'FSM' => 'Micronesia Federated States of',
            'MDA' => 'Moldova Republic of',
            'MCO' => 'Monaco',
            'MNG' => 'Mongolia',
            'MNE' => 'Montenegro',
            'MSR' => 'Montserrat',
            'MAR' => 'Morocco',
            'MOZ' => 'Mozambique',
            'MMR' => 'Myanmar',
            'NAM' => 'Namibia',
            'NPL' => 'Nepal',
            'NLD' => 'Netherlands',
            'NZL' => 'New Zealand',
            'NIC' => 'Nicaragua',
            'NER' => 'Niger',
            'NGA' => 'Nigeria',
            'NOR' => 'Norway',
            'OMN' => 'Oman',
            'PAK' => 'Pakistan',
            'PLW' => 'Palau',
            'PSE' => 'Palestinian Occupied Territory',
            'PAN' => 'Panama',
            'PNG' => 'Papua New Guinea',
            'PRY' => 'Paraguay',
            'PER' => 'Peru',
            'PHL' => 'Philippines',
            'POL' => 'Poland',
            'PRT' => 'Portugal',
            'PRI' => 'Puerto Rico',
            'QAT' => 'Qatar',
            'ROU' => 'Romania',
            'RUS' => 'Russian Federation',
            'RWA' => 'Rwanda',
            'KNA' => 'Saint Kitts and Nevis',
            'LCA' => 'Saint Lucia',
            'VCT' => 'Saint Vincent and the Grenadines',
            'SMR' => 'San Marino',
            'STP' => 'Sao Tome and Principe',
            'SAU' => 'Saudi Arabia',
            'SEN' => 'Senegal',
            'SRB' => 'Serbia',
            'SYC' => 'Seychelles',
            'SLE' => 'Sierra Leone',
            'SGP' => 'Singapore',
            'SVK' => 'Slovakia',
            'SVN' => 'Slovenia',
            'ZAF' => 'South Africa',
            'SSD' => 'South Sudan',
            'ESP' => 'Spain',
            'LKA' => 'Sri Lanka',
            'SDN' => 'Sudan',
            'SUR' => 'Suriname',
            'SWZ' => 'Swaziland',
            'SWE' => 'Sweden',
            'CHE' => 'Switzerland',
            'SYR' => 'Syrian Arab Republic',
            'TWN' => 'Taiwan',
            'TJK' => 'Tajikistan',
            'TZA' => 'Tanzania United Republic of',
            'THA' => 'Thailand',
            'TLS' => 'Timor Leste',
            'TGO' => 'Togo',
            'TON' => 'Tonga',
            'TTO' => 'Trinidad and Tobago',
            'TUN' => 'Tunisia',
            'TUR' => 'Turkey',
            'TKM' => 'Turkmenistan',
            'TCA' => 'Turks and Caicos Islands',
            'UGA' => 'Uganda',
            'UKR' => 'Ukraine',
            'ARE' => 'United Arab Emirates',
            'GBR' => 'United Kingdom',
            'URY' => 'Uruguay',
            'UZB' => 'Uzbekistan',
            'VUT' => 'Vanuatu',
            'VEN' => 'Venezuela Bolivarian Republic of',
            'VNM' => 'Vietnam',
            'VGB' => 'Virgin Islands British',
            'VIR' => 'Virgin Islands US',
            'YEM' => 'Yemen',
            'ZMB' => 'Zambia',
            'ZWE' => 'Zimbabwe',
        ];

        return $code ? $nations[$code] : $nations;
    }
}

if (!function_exists('getMonths')) {
    /**
     * @return array
     */
    function getMonths()
    {
        return \Illuminate\Support\Facades\Cache::remember('months', 60, function () {
            return [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December',
            ];
        });
    }
}

if (!function_exists('parameter')) {
    /**
     * Parameter helper
     *
     * @param string $key
     * @param $defaultvalue | optional
     * @return string| \Buzzex\Models\Parameter
     */
    function parameter($key = false, $defaultValue = false)
    {
        $setting = app(\Buzzex\Contracts\Setting\ManageParameter::class);

        if ($key === false) {
            return $setting;
        }

        $value = $setting->get($key);

        return !is_null($value) ? $value : $defaultValue;
    }
}

if (!function_exists('languages')) {
    /**
     * Get the full name of language by code or return the language list if no code
     *
     * @param $code | optional
     *
     * @return string
     */
    function languages($code = '')
    {
        $language_list = [
            'en' => 'English',
            'ph' => 'Filipino',
        ];

        if (empty($code)) {
            return $language_list;
        }

        return array_key_exists($code, $language_list) ? $language_list[$code] : "";
    }
}

if (!function_exists('maximumFileUploadSize')) {
    /**
     * This function returns the maximum files size that can be uploaded
     * in PHP
     *
     * @return int File size in bytes
     */
    function maximumFileUploadSize()
    {
        return min(
            convertPHPSizeToBytes(ini_get('post_max_size')),
            convertPHPSizeToBytes(ini_get('upload_max_filesize'))
        );
    }
}

if (!function_exists('convertPHPSizeToBytes')) {
    /**
     * This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
     *
     * @param  string $sSize
     *
     * @return integer The value in bytes
     */
    function convertPHPSizeToBytes($sSize)
    {
        $sSuffix = strtoupper(substr($sSize, -1));
        if (!in_array($sSuffix, ['P', 'T', 'G', 'M', 'K'])) {
            return (int)$sSize;
        }
        $iValue = substr($sSize, 0, -1);
        switch ($sSuffix) {
            case 'P':
                $iValue *= 1024;
            // Fallthrough intended
            // no break
            case 'T':
                $iValue *= 1024;
            // Fallthrough intended
            // no break
            case 'G':
                $iValue *= 1024;
            // Fallthrough intended
            // no break
            case 'M':
                $iValue *= 1024;
            // Fallthrough intended
            // no break
            case 'K':
                $iValue *= 1024;
                break;
        }

        return (int)$iValue;
    }
}


if (!function_exists('exchangeTypeOptions')) {
    /**
     * @return array
     */
    function exchangeTypeOptions()
    {
        return [
            'Fiat Currency',
            'Cryptocurrency',
            'Commodity',
            'Digital Commodity',
            'Token - ADZbuzz Community',
            'Token - Other',
        ];
    }
}

if (!function_exists('assetsOptions')) {
    /**
     * @return array
     */
    function assetsOptions()
    {
        return [
            'all',
            'deposit',
            'withdraw',
            'exchange',
            'system',
            'gift',
            'refer',
            'transfer',
            'convert',
        ];
    }
}

if (!function_exists('exchangeTxnStatuses')) {
    /**
     * @return array
     */
    function exchangeTxnStatuses()
    {
        return [
            'pending'=>'Pending',
            'approved'=>'Approved',
            'released'=>'Released',
            'processed' => 'Processed',
            'cancelled'=>'Cancelled',
        ];
    }
}

if (!function_exists('getTxnStatus')) {

    /**
     * @return string
     */

    function getTxnStatus($cancelled = 0, $approved = 0, $released = 0, $processed = 0)
    {
        if ($cancelled == 0 && $approved == 0 && $released == 0) {
            return "Pending";
        }

        if ($cancelled > 0 && $approved == 0 && $released == 0) {
            return "Cancelled";
        }

        if ($cancelled == 0 && $released == 0 && $processed > 0 && $approved > 0) {
            return "Processed";
        }
        
        if ($cancelled == 0 && $approved > 0 && $released == 0) {
            return "Approved";
        }


        if ($cancelled == 0 && $released > 0) {
            return "Released";
        }
    }
}

if (!function_exists('getMinimumSellAmount')) {
    /**
     * Get minimum sell amount
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    function getMinimumSellAmount()
    {
        // @todo check db and return what set
        return config('trading.minimum_sell_amount');
    }
}

if (!function_exists('getMinimumBuyAmount')) {
    /**
     * Get minimum buy amount
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    function getMinimumBuyAmount()
    {
        // @todo check db and return what set
        return config('trading.minimum_buy_amount');
    }
}


if (!function_exists('isDepositAddressAvailable')) {
    /**
     * @param string $coinName
     *
     * @return string|bool
     */
    function isDepositAddressAvailable($coinName)
    {
        $coinName = strtolower(trim($coinName));

        $addressTable = $coinName . '_addresses';

        if (!Illuminate\Support\Facades\Schema::hasTable($addressTable)) {
            return false;
        }

        return Illuminate\Support\Facades\DB::table($addressTable)
            ->select('address_id')
            ->where('status_id', 0)
            ->count();
    }
}

if (!function_exists('get_from_server')) {
    /**
     * @param string $url
     *
     * @throws ErrorException
     */
    function get_from_server($url)
    {
        $curl = new Curl\Curl();

        $curl->setOpt(CURLOPT_AUTOREFERER, true);
        $curl->setOpt(CURLOPT_HEADER, 0);
        $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curl->setOpt(CURLOPT_TIMEOUT, 10);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);

        $curl->get($url);

        return ($curl->error) ? false : $curl->response;
    }
}

if (!function_exists('reduceImageSize')) {
    /**
     * Optimize image
     *
     * @param File $file
     * @param Encoding $encode
     * @param Scaling $scale
     * @param Quality $quality
     *
     * @return File
     */
    function reduceImageSize($file, $filename = '', $encode = 'jpp', $scale = '50', $quality = '100')
    {
        $scale = $scale / 100;

        if (!$filename) {
            $filename = 'Optimized-image-' . Carbon::now()->toDateString();
        }

        $filename = "assets/images/$filename";

        $file = Image::make($file);

        if ($file->filesize() > 2000000) {
            $file->resize($file->width() * $scale, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }

        $file->encode($encode, $quality)->save($filename);

        return new File($filename);
    }
}

if (!function_exists('makeImage')) {
    /**
     * @param File $file
     * @param String $width
     * @param Encoding $encode
     */
    function makeImage($file, $resize = true, $width = 500, $encode = 'jpg')
    {
        if (!$file || is_null($file)) {
            return '';
        }

        $image = Image::make($file);

        if ($resize) {
            $image->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }

        // Return image by specified width and maintain aspect ratio as base64
        return "data:image/$encode;base64," . base64_encode($image->encode($encode, 100)->stream());
    }
}

if (!function_exists('hmac256')) {
    /**
     * @param string $key
     * @param string|array $data
     *
     * @return string
     */
    function hmac256($key, $data, $code='sha256')
    {
        if (is_array($data)) {
            $data = http_build_query($data, '', '&');
        }
        
        return hash_hmac($code, $data, $key);
    }
}


if (!function_exists('isAuthorizedWithdrawalProcessor')) {
    /**
     * @param string $key
     */
    function isAuthorizedWithdrawalProcessor($key)
    {
        $apiKeys = trim(config('auth.withdrawal_processor_keys'));

        if ($apiKeys === '') {
            $apiKeys = [];
        } else {
            $apiKeys = explode(',', $apiKeys);
        }

        return in_array($key, $apiKeys);
    }
}

 
if (!function_exists('formatMarket')) {
    /**
     * format market according to service
     *
     * @return string
     */
    function formatMarket($pair, $old_delimiter, $new_delimiter, $reverse=false)
    {
        $coin = explode($old_delimiter, $pair);
        if ($reverse) {
            arsort($coin);
        } // incase service uses different format
        return implode($new_delimiter, $coin);
    }
}
 
if (!function_exists('getPercentageOfNumber')) {
    /**
     * @param numeric $number
     * @param numeric $percent
     */
    function getPercentageOfNumber($number, $percent)
    {
        if ($number > 0) {
            return ($percent / 100) * $number;
        }
        
        return 0;
    }
}


if (!function_exists('getPairTextInfo')) {
    /**
     * @param string $pair_text
     */
    function getPairTextInfo($pair_text)
    {
        return \Buzzex\Models\ExchangePairStat::where('pair_text', $pair_text)->first();
    }
}

if (!function_exists('getApiProfitMargin')) {
    /**
     * @param string $service
     */
    function getApiProfitMargin($service)
    {
        if (Cache::has('api-profit-margin-'.$service)) {
            return Cache::get('api-profit-margin-'.$service);
        }
        $value = 0;

        $margin = \Buzzex\Models\ExchangeApi::where('name', strtolower($service))->pluck('profit_margin');
        
        if ($margin && isset($margin[0])) {
            $value = $margin[0]/100;
        }
        Cache::put('api-profit-margin-'.$service, $value, now()->addMinutes(30));
        
        return $value;
    }
}

if (!function_exists('personalWalletOption')) {
    /**
     *
     * @return string | mixed
     */
    function personalWalletOption($value = null, $save = true)
    {
        $user = Auth()->user();
        $key = "personal-wallet-option-$user->id";

        if (!$save) {
            if (Cache::has($key)) {
                return Cache::get($key);
            }
        }

        if (Cache::has($key)) {
            $option = Cache::get($key);
            
            if ($option != $value) {
                Cache::forget($key);
                Cache::forever($key, $value);
                $option = $value;
            }
        } else {
            Cache::forever($key, $value);
            $option = $value;
        }

        return $option;
    }
}

if (!function_exists('getMyInsertedExternalOrder')) {
    /**
     * @param object $object
     */
    function getMyInsertedExternalOrder($object)
    {
        $external_type = $object->type == 'BUY' ? 'SELL':'BUY';

        return Buzzex\Models\ExchangeOrder::where('user_id', parameter('external_exchange_order_user_id'))
        ->where('type', $external_type)
        ->where('amount', $object->amount)
        ->where('price', $object->price)
        ->first();
    }
}

if (!function_exists('addLocalSellToSessionStorage')) {
    /**
     * @param array $array
     */
    function addLocalSellToSessionStorage($array)
    {
        $current_orders = [];
        if ($orders = json_decode($array)) {
            foreach ($orders as $key => $order) {
                $current_orders[] = [
                    $order->price,
                    $order->amount
                ];
            }
        }
        return json_encode($current_orders);
    }
}

if (!function_exists('getExchangeApis')) {
    /**
     * @param array $array
     */
    function getExchangeApis()
    {
        $data = [];
        $apis = Buzzex\Models\ExchangeApi::get();
        foreach ($apis as $key => $value) {
            $data[$value->id] = ucfirst($value->name);
        }
        return $data;
    }
}

if (!function_exists('blurEmail')) {
    /**
     * @param String $email
     */
    function blurEmail($email = '')
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          return $email;
        }

        $email = explode('@', $email);
        //@todo create catcher.

        $email[0] = substr($email[0], 0, 2)
                    .str_repeat('*', strlen($email[0]) - 4)
                    .substr($email[0], strlen($email[0]) - 2, 2);

        $email[1] = substr($email[1], 0, 2)
                    .str_repeat('*', strlen($email[1]) - strrpos($email[1], '.'))
                    .substr($email[1], (strlen($email[1]) - (strlen($email[1]) - strrpos($email[1], '.'))), strlen($email[1]) - strrpos($email[1], '.'));

        return implode('@', $email);
    }
}


if (!function_exists('milestoneOptions')) {
    /**
     * @param array $array
     */
    function milestoneOptions()
    {
        return Buzzex\Models\CoinCompetition::get();
    }
}


if (! function_exists('str_ordinal')) {
    /**
     * Append an ordinal indicator to a numeric value.
     *
     * @param  string|int  $value
     * @param  bool  $superscript
     * @return string
     */
    function str_ordinal($value, $superscript = false)
    {
        $number = abs($value);
 
        $indicators = ['th','st','nd','rd','th','th','th','th','th','th'];
 
        $suffix = $superscript ? '<sup>' . $indicators[$number % 10] . '</sup>' : $indicators[$number % 10];
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            $suffix = $superscript ? '<sup>th</sup>' : 'th';
        }
 
        return number_format($number) . $suffix;
    }
}

if (! function_exists('getGeneralPrizes')) {
    /**
     * @return array
     */
    function getGeneralPrizes()
    {
        return [
            40000,
            30000,
            20000,
            1000,
            1000,
            1000,
            1000,
            1000,
            1000,
            1000
        ];
    }
}
