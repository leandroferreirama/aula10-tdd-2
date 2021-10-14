<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EnviadorEmail
{
    public function notificarTerminoLeilao(Leilao $leilao)
    {
        $sucesso = mail('suporte@integracaosistema.com.br', 'Leilão Finalizado', 
        'O leilao para ' . $leilao->recuperarDescricao() . ' foi finalizado');

        if(!$sucesso){
            throw new \DomainException('Erro ao enviar e-mail!');
        }
    }
}