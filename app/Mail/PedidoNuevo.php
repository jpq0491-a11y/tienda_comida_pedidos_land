<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso al administrador de que llegó un pedido nuevo.
 * Se envía al confirmar el checkout (CheckoutController@store).
 * En la Etapa 9 se manda en cola agregando "implements ShouldQueue".
 */
class PedidoNuevo extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Las propiedades públicas llegan solas a la vista del correo: ahí se usa $pedido.
     */
    public function __construct(public Pedido $pedido)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pedido nuevo {$this->pedido->folio} por \${$this->formatoTotal()}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'correos.pedido-nuevo',
        );
    }

    private function formatoTotal(): string
    {
        return number_format($this->pedido->total, 2);
    }
}
