<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfCnpj implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if (strlen($digits) === 11) {
            if (! $this->validCpf($digits)) {
                $fail('O CPF informado é inválido.');
            }

            return;
        }

        if (strlen($digits) === 14) {
            if (! $this->validCnpj($digits)) {
                $fail('O CNPJ informado é inválido.');
            }

            return;
        }

        $fail('O documento deve ser um CPF ou CNPJ válido.');
    }

    private function validCpf(string $cpf): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;

            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ((int) $cpf[$c] !== $d) {
                return false;
            }
        }

        return true;
    }

    private function validCnpj(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $first = $this->cnpjDigit($cnpj, $weights1);
        $second = $this->cnpjDigit($cnpj, $weights2);

        return (int) $cnpj[12] === $first && (int) $cnpj[13] === $second;
    }

    private function cnpjDigit(string $cnpj, array $weights): int
    {
        $sum = 0;

        foreach ($weights as $index => $weight) {
            $sum += (int) $cnpj[$index] * $weight;
        }

        $rest = $sum % 11;

        return $rest < 2 ? 0 : 11 - $rest;
    }
}
