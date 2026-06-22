<?php

namespace App\Services\Agenda\Exceptions;

use RuntimeException;

/**
 * Lançada quando um horário deixou de estar disponível (ocupado/inválido)
 * no momento da revalidação transacional. É um erro recuperável: o fluxo
 * deve oferecer novas alternativas.
 */
class SlotUnavailableException extends RuntimeException {}
