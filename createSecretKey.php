<?php

// Генерация секретного ключа
echo base64_encode(bin2hex(random_bytes(32)));
