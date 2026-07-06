<?php

if (! function_exists('euro')) {
    /**
     * Formatea un importe en euros con el formato español: 1.234,56 €
     */
    function euro(mixed $amount): string
    {
        return number_format((float) $amount, 2, ',', '.') . ' €';
    }
}
