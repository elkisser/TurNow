<?php

namespace App\Services;

use App\Entity\Turno;
use App\Entity\Administrador;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Notificar al administrador sobre nuevo turno
     */
    public function notificarNuevoTurno(Turno $turno): void
    {
        $administrador = $turno->getServicio()->getAdministrador();
        
        $email = (new Email())
            ->from('notificaciones@turnow.com')
            ->to($administrador->getEmail())
            ->subject('Nuevo turno reservado - TurNow')
            ->html($this->generarTemplateNotificacionAdmin($turno));

        $this->mailer->send($email);
    }

    private function generarTemplateNotificacionAdmin(Turno $turno): string
    {
        return sprintf(
            '<h1>Nuevo Turno Reservado</h1>
            <p>Se ha reservado un nuevo turno en su servicio.</p>
            <p><strong>Servicio:</strong> %s</p>
            <p><strong>Fecha:</strong> %s</p>
            <p><strong>Hora:</strong> %s</p>
            <p><strong>Cliente:</strong> %s</p>
            <p><strong>Estado:</strong> %s</p>
            <br>
            <p>Acceda a su panel de administración para más detalles.</p>',
            $turno->getServicio()->getNombre(),
            $turno->getFecha()->format('d/m/Y'),
            $turno->getHora()->format('H:i'),
            $turno->getUsuarioEmail(),
            $turno->getEstado()
        );
    }

    /**
     * Notificar cancelación de turno
     */
    public function notificarCancelacionTurno(Turno $turno): void
    {
        $administrador = $turno->getServicio()->getAdministrador();
        
        $email = (new Email())
            ->from('notificaciones@turnow.com')
            ->to($administrador->getEmail())
            ->subject('Turno cancelado - TurNow')
            ->html($this->generarTemplateCancelacionAdmin($turno));

        $this->mailer->send($email);
    }

    private function generarTemplateCancelacionAdmin(Turno $turno): string
    {
        return sprintf(
            '<h1>Turno Cancelado</h1>
            <p>Un turno ha sido cancelado.</p>
            <p><strong>Servicio:</strong> %s</p>
            <p><strong>Fecha:</strong> %s</p>
            <p><strong>Hora:</strong> %s</p>
            <p><strong>Cliente:</strong> %s</p>
            <br>
            <p>El turno ahora está disponible para nuevas reservas.</p>',
            $turno->getServicio()->getNombre(),
            $turno->getFecha()->format('d/m/Y'),
            $turno->getHora()->format('H:i'),
            $turno->getUsuarioEmail()
        );
    }
}