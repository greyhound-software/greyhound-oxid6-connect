<?php
/*
 * GREYHOUND OXID Connect module for OXID eShop
 *
 * MIT License
 *
 * Copyright (c) 2019 GREYHOUND Software
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

$aLang = array(
    'charset'                                 => 'UTF-8',
    'SHOP_MODULE_GROUP_api'                   => 'Schnittstelle zum GREYHOUND Addon',
    'SHOP_MODULE_sGhApiKey'                   => 'API Key',
    'HELP_SHOP_MODULE_sGhApiKey'              => 'Legen sie hier einen beliebiges Passwort für den Zugriff durch das GREYHOUND OXID Connect Addon fest. Dieses Passwort müssen sie dann ebenfalls in GREYHOUND in den Einstellungen des OXID Connect Addons eintragen, damit das Addon Daten aus dem Shop abrufen kann. Wenn das Passwort leer ist, so ist kein Zugriff durch das GREYHOUND OXID Connect Addon möglich.',
    'SHOP_MODULE_sGhApiUrl'                   => 'API URL',
    'HELP_SHOP_MODULE_sGhApiUrl'              => 'Dieser Wert wird automatisch ermittelt und ist nicht änderbar. Er wird wird die Addon Einstellungen in GREYHOUND benötigt.',
    'SHOP_MODULE_blGhApiAllowNonSsl'          => 'Unsichere Verbindung zulassen',
    'HELP_SHOP_MODULE_blGhApiAllowNonSsl'     => 'Ist diese Option aktiviert, so antwortet die API auch über unsichere Verbindungen, ansonsten antwortet sie nur über SSL Verbindungen. Diese Option sollte nur zur Problemanalyse aktiviert werden. Im Produktivbetrieb sollten keine unsicheren Verbindungen akzeptiert werden, da über die API Kunden- und Auftragsdaten übertragen werden.',
    'SHOP_MODULE_blGhApiIncludeSubshops'      => 'Daten der Subshops über die API des Hauptshops zur Verfügung stellen',
    'HELP_SHOP_MODULE_blGhApiIncludeSubshops' => 'Wenn diese Option aktiviert ist, dann werden bei Abfragen der API des Hauptshops auch Daten aus den Subshops ausgeliefert. Im GREYHOUND Addon muss dann nur eine Verbindung zum Hauptshop eingetragen werden.',
);
