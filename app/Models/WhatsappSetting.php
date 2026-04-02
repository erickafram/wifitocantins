<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WhatsappSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Obter valor de uma configuração
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Definir valor de uma configuração
     */
    public static function set($key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Verificar se está conectado
     */
    public static function isConnected()
    {
        return static::get('is_connected') === 'true';
    }

    /**
     * Obter status da conexão
     */
    public static function getConnectionStatus()
    {
        return static::get('connection_status', 'disconnected');
    }

    /**
     * Obter telefone conectado
     */
    public static function getConnectedPhone()
    {
        return static::get('connected_phone');
    }

    /**
     * Obter template da mensagem
     */
    public static function getMessageTemplate()
    {
        return static::get('message_template', "Olá! 👋\n\nVocê ainda não efetuou seu pagamento.\n\nPara navegar durante sua viagem, pague apenas *R$ 5,99* e tenha internet à vontade! 🚀\n\n📱 Acesse: http://10.5.50.1/login\n\nWiFi Tocantins - Internet na sua viagem!");
    }

    /**
     * Obter minutos de pendência para envio
     */
    public static function getPendingMinutes()
    {
        return (int) static::get('pending_minutes', 15);
    }

    /**
     * Verificar se envio automático está habilitado
     */
    public static function isAutoSendEnabled()
    {
        return static::get('auto_send_enabled') === 'true';
    }

    /**
     * Verificar se envio automatico de avaliacao esta habilitado
     */
    public static function isReviewAutoSendEnabled()
    {
        return static::get('review_auto_send_enabled', 'true') === 'true';
    }

    /**
     * Obter template da mensagem de avaliacao
     */
    public static function getReviewMessageTemplate()
    {
        return static::get(
            'review_message_template',
            "Ola, {nome}! Queremos saber sua opiniao sobre o atendimento e o servico oferecido durante a viagem.\n\nLeva menos de 20 segundos para responder:\n{link}\n\nSua nota vai de 1 a 5 estrelas. Se a nota for 1, 2 ou 3, voce podera contar o que aconteceu.\n\nData da viagem: {data_viagem}"
        );
    }

    /**
     * Obter QR Code
     */
    public static function getQrCode()
    {
        return static::get('last_qr_code');
    }

    /**
     * Atualizar status de conexão
     */
    public static function updateConnectionStatus($status, $phone = null)
    {
        static::set('connection_status', $status);
        static::set('is_connected', $status === 'connected' ? 'true' : 'false');
        
        if ($phone) {
            static::set('connected_phone', $phone);
        } elseif ($status !== 'connected') {
            static::set('connected_phone', null);
        }
    }
}
