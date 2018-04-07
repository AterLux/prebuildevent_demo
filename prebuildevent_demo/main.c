/*
 * prebuildevent_demo.c
 *
 * Author: Погребняк Дмитрий (Pogrebnyak Dmitry, http://aterlux.ru/)
 */ 

#define F_CPU 1000000UL

#include <avr/io.h>
#include <avr/pgmspace.h>
#include <util/delay.h>

#include "display/display.h"
#include "build_version.h"

typedef uint8_t image_data_t[8][128];

// Подключаем сгенерированный файл
#include "generated/images.inc"


int main(void)
{
  display_init();
  uint8_t img = 0;
  for(;;) {
    image_data_t * p_img = pgm_read_ptr(&list_img[img]);
    for (uint8_t p = 0 ; p < 8 ; p++) {
      display_sprite(p, 0, &(*p_img)[p], 128, 0);
    }
    img++;
    if (img >= (sizeof(list_img) / sizeof(list_img[0]))) img = 0;
    _delay_ms(3000);
  }
}

