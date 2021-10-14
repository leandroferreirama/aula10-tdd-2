<?php
namespace Alura\Leilao\Tests\Unit\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorEmail;
use PHPUnit\Framework\TestCase;

class EcerradorTest extends TestCase
{
    private $encerrador;
    private $fiat147;
    private $variant;
    private $enviadorEmail;


    protected function setUp(): void
    {
        $this->fiat147 = new Leilao('Fiat 147 0KM',new \DateTimeImmutable('8 days ago'));
        $this->variant = new Leilao('this->Variant 1972 0km', new \DateTimeImmutable('10 days ago'));

        $leilaoDao = $this->createMock(LeilaoDao::class);
        $leilaoDao->method('recuperarNaoFinalizados')->willReturn([$this->fiat147, $this->variant]);

        $leilaoDao->expects($this->exactly(2))->method('atualiza')->withConsecutive([$this->fiat147],[$this->variant]);

        $this->enviadorEmail = $this->createMock(EnviadorEmail::class);
        $this->encerrador = new Encerrador($leilaoDao, $this->enviadorEmail);
    }
    
    /** @test */
    public function leiloesComMaisDeUmaSemanaDeveSerEncerrado()
    {
        
        $this->encerrador->encerra();

        $leiloes = [$this->fiat147, $this->variant];
 
        self::assertCount(2, $leiloes);
        self::assertTrue($leiloes[0]->estaFinalizado());
        self::assertTrue($leiloes[1]->estaFinalizado());
    }

    /** @test */
    public function processoDeEncerramentoDeveContinuarMesmoOcorrendoErro()
    {
        $e = new \DomainException('Erro ao enviar e-mail!');
        $this->enviadorEmail->expects($this->exactly(2))->method('notificarTerminoLeilao')->willThrowException($e);
        $this->encerrador->encerra();
    }

    /** @test */
    public function soDeveEnviarLeilaoPorEmailAposFinalizado()
    {
        $this->enviadorEmail->expects($this->exactly(2))->method('notificarTerminoLeilao')->willReturnCallback(function(Leilao $leilao){
            self::assertTrue($leilao->estaFinalizado());
        });

        $this->encerrador->encerra();
    }
}