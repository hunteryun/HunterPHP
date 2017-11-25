<?php

namespace Hunter\Core\Utility;

use Hunter\Core\Transliteration\PhpTransliteration;

class StringConverter {
    /**
     * The transliteration helper.
     *
     * @var \Hunter\Core\Transliteration\TransliterationInterface
     */
    protected $transliteration;

    /**
     * Constructs a MachineNameController object.
     *
     * @param \Hunter\Core\Transliteration\TransliterationInterface $transliteration
     *   The transliteration helper.
     */
    public function __construct() {
      $this->transliteration = new PhpTransliteration();
    }

    /**
     * Replaces non alphanumeric characters with underscores.
     *
     * @param String $name User input
     *
     * @return String $machine_name User input in machine-name format
     */
    public function createMachineName($text)
    {
        $machine_name = Unicode::strtolower($this->transliteration->transliterate($text, 'en', '_'));
        $machine_name = preg_replace('@' . strtr("[^a-z0-9_]+", ['@' => '\@', chr(0) => '']) . '@', '_', $machine_name);

        return $machine_name;
    }

}
