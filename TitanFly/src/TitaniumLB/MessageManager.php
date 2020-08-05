<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2020/07/28
 * Time: 22:00
 */

namespace TitaniumLB\TitanFly;


use pocketmine\utils\TextFormat;

class MessageManager
{
    /** @var array */
    protected $rawMessages = [];

    /** @var array */
    protected $messages = [];

    public function __construct(array $messages) {
        $this->rawMessages = $messages;
        $this->parseMessages();
    }

    protected function parseMessages() {
        foreach($this->rawMessages as $key => $raw) {
            $this->messages[strtolower($key)] = $this->parseMessage($raw);
        }
    }

    /**
     * @param string $message
     * @param string $symbol
     *
     * @return mixed|string
     */
    public function parseMessage(string $message, $symbol = "&") {
        return TextFormat::colorize($message, $symbol);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getMessage($key) {
        return $this->messages[strtolower($key)];
    }

}