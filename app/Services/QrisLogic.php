<?php

namespace App\Services;

class QrisLogic
{
    /**
     * Calculate CRC16 Checksum (CCITT-FALSE)
     * Matches the implementation in the original TypeScript/PHP code.
     *
     * @param string $str
     * @return string
     */
    public static function crc16($str)
    {
        $crc = 0xFFFF;
        $strlen = strlen($str);
        
        for ($c = 0; $c < $strlen; $c++) {
            $crc ^= ord($str[$c]) << 8;
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }
        
        $hex = strtoupper(dechex($crc & 0xFFFF));
        return str_pad($hex, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Genereate Dynamic QRIS String
     *
     * @param string $staticQris
     * @param string|int $amount
     * @param string $feeType 'Persentase' or 'Rupiah'
     * @param string|int $feeValue
     * @return string
     * @throws \Exception
     */
    public static function generateDynamicQris($staticQris, $amount, $feeType = 'Persentase', $feeValue = 0)
    {
        if (strlen($staticQris) < 4) {
            throw new \Exception('Invalid static QRIS data.');
        }

        // 1. Prepare Base String (Remove CRC)
        $qrisWithoutCrc = substr($staticQris, 0, -4);
        
        // 2. Change Type to Dynamic (010211 -> 010212)
        // Note: Using str_replace might be risky if "010211" appears elsewhere, 
        // but typically it's at the start. 
        // A safer way is ensuring we only replace the first occurrence or specific position.
        // For now, mirroring the original logic:
        $step1 = str_replace("010211", "010212", $qrisWithoutCrc);

        // 3. Split by Merchant ID Key (5802ID)
        // This splits the string into [Header+Tags before 58, Tags after 58]
        $parts = explode("5802ID", $step1);
        if (count($parts) !== 2) {
            throw new \Exception("QRIS data is not in the expected format (missing '5802ID').");
        }

        // 4. Create Amount Tag (Tag 54)
        $amountStr = (string)intval($amount); 
        $amountTag = "54" . str_pad(strlen($amountStr), 2, '0', STR_PAD_LEFT) . $amountStr;

        // 5. Create Fee Tag (Tag 55) - Optional
        $feeTag = "";
        if ($feeValue && floatval($feeValue) > 0) {
            if ($feeType === 'Rupiah') {
                $feeValueStr = (string)intval($feeValue);
                // 55 -> 02 -> 56 -> length -> value
                $feeTag = "55020256" . str_pad(strlen($feeValueStr), 2, '0', STR_PAD_LEFT) . $feeValueStr;
            } else { // Persentase
                $feeValueStr = (string)$feeValue;
                // 55 -> 02 -> 03 -> 57 -> length -> value
                $feeTag = "55020357" . str_pad(strlen($feeValueStr), 2, '0', STR_PAD_LEFT) . $feeValueStr;
            }
        }

        // 6. Assemble Payload
        // Re-insert 5802ID in the middle
        $payload = $parts[0] . $amountTag . $feeTag . "5802ID" . $parts[1];

        // 7. Calculate New Checksum
        $finalCrc = self::crc16($payload);

        return $payload . $finalCrc;
    }

    /**
     * Extract Merchant Name (Tag 59)
     * 
     * @param string $qrisData
     * @return string
     */
    public static function parseMerchantName($qrisData)
    {
        $tag = '59';
        $tagIndex = strpos($qrisData, $tag);
        
        if ($tagIndex === false) {
            return 'Merchant';
        }

        try {
            $lengthIndex = $tagIndex + strlen($tag);
            // Length is 2 chars
            $lengthStr = substr($qrisData, $lengthIndex, 2);
            $length = intval($lengthStr);

            if ($length <= 0) {
                return 'Merchant';
            }

            $valueIndex = $lengthIndex + 2;
            $merchantName = substr($qrisData, $valueIndex, $length);
            
            return trim($merchantName) ?: 'Merchant';
        } catch (\Exception $e) {
            return 'Merchant';
        }
    }
}
