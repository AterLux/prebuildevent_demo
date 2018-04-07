/*
 * display.c
 *
 * Author: Погребняк Дмитрий (Pogrebnyak Dmitry, http://aterlux.ru/)
 */ 

#include "display.h"

/* Заполняет экран заданными чередующимися значениями */
uint8_t display_fill(uint8_t evenData, uint8_t oddData) {
  for (uint8_t p = 0 ; p < DISPLAY_PAGES; p++) {
    if (!display_setpos(p, 0)) return 0;
    for (uint8_t x = DISPLAY_WIDTH / 2; x; x--) {
      if (!display_data(evenData) || !display_data(oddData)) return 0;
    }    
    #if ((DISPLAY_WIDTH & 1) != 0)
      if (!display_data(oddData)
    #endif
  }
  return 1;
}

/* Очищает экран, устанавливает курсор в левый верхний угол */
uint8_t display_clear() {
  return display_fill(0, 0) && display_setpos(0, 0);
}

/* Выводит на экран строку из флеш-памяти
 * page - строка на которую выводится изображение
 * x - позиция, начиная с которой выводится изображение
 * pData - ссылка на данные во флеш-памяти
 * width - количество выводимых данных, ширина
 * xorMask - инверсия, применяемая к выводимым данным.
 * 
 * Если изображение не помещается на экран по ширине, выводит только его часть. Возвращает сколько колонок было выведено
 */
uint8_t display_sprite(uint8_t page, uint8_t x, PGM_VOID_P pData, uint8_t width, uint8_t xorMask) {
  if ((page >= DISPLAY_PAGES) || (x >= DISPLAY_WIDTH))
    return 0;
  if (!display_setpos(page, x)) return 0;
  uint8_t r = DISPLAY_WIDTH - x;
  if (width > r)
    width = r;
  else 
    r = width;
  while (width--) {
    if (!display_data(pgm_read_byte(pData++) ^ xorMask)) return 0;
  }  
  return r;
}

/* Копирует из буфера изображение на экран
 * page, x - начальная позиция для вывода буфера
 * pData - указатель на буфер
 * pages_height - высота буфера (в страницах)
 * width - ширина буфера
 */
uint8_t display_buffer(uint8_t page, uint8_t x, uint8_t * pData, uint8_t pages_height, uint8_t width) {
  if ((x >= DISPLAY_WIDTH) || (page >= DISPLAY_PAGES))
    return 0;
  uint8_t w = DISPLAY_WIDTH - x;
  if (w > width)
    w = width;
  uint8_t t = page + ((pages_height > DISPLAY_PAGES) ? DISPLAY_PAGES : pages_height);
  if (t > DISPLAY_PAGES)
    t = DISPLAY_PAGES;
  while (page < t) {
    if (!display_setpos(page++, x)) return 0;
    for (uint8_t i = 0; i < w; i++) {
      if (!display_data(pData[i])) return 0;
    }
    pData += width;
  }
  return 1;
}
