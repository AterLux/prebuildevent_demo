﻿/*
 * SH1106.h
 *
 * Author: Погребняк Дмитрий (Pogrebnyak Dmitry, http://aterlux.ru/)
 */ 


#ifndef SSD1306
#define SSD1306

#include "displayhw.h"

#include <avr/io.h>
#include <avr/pgmspace.h>

// Ширина дисплея в пикселях
#define DISPLAY_WIDTH 128

// Высота дисплея, в страницах. Каждая страница - 8 пикселей.
// Укажите 8 для дисплеев высотой 64 пикселя, 4 - для дисплеев высотой 32 пикселя
#define DISPLAY_PAGES 8

// Высота дисплея в пикселях
#define DISPLAY_HEIGHT (DISPLAY_PAGES * 8)

// Максимальное значение контрастности
#define DISPLAY_CONTRAST_MAX_VAL 255



// Если изображение вверх ногами, поменяйте значение SSD1306_FLIP_VERTICALLY и SSD1306_FLIP_HORIZONTALLY 

// Отражение изображения вертикально: 1 - отражённое, 0 - обычное
#define SSD1306_FLIP_VERTICALLY 1

// Отражение изображения горизонтально: 1 - отражённое, 0 - обычное
#define SSD1306_FLIP_HORIZONTALLY 1


// Начальное значение контраста 
#define SSD1306_CONTRAST_INITIAL_VALUE 0x80


// ВНИМАНИЕ! Параметры ниже влияют на работу дисплея и обычно их менять не нужно

// Черезстрочный режим: 1 - включено, 0 - отключено
// Если похоже что чётные и нечётные строки выводятся в разных участках экрана, или неестественно чередуются, поменяйте это значение
#define SSD1306_COM_PINS_INTERLEAVED 1

// Режим смены верхней и нижней части: 1 - включено, 0 - отключено
// Если верхняя и нижняя половины изображения перепутаны местами, или при включении черезстрочного режима, чётные и нечётные строки 
// перепутаны местами, поменяйте это значение 
#define SSD1306_COM_HALVES_CHANGED 0


// Если при использовании дисплея меньше 64 пикселей в высоту кажется что отображается не та часть изображения, попробуйте изменить это значение на половину высоты
#define SSD1306_DISPLAY_OFFSET 0

// Настройка делителя частоты тактового генератора. Рекомендуемое значение 1
#define SSD1306_CLOCK_RATIO 1

// Калибровка частоты осциллятора тактового генератора. Рекомендуемое значение 8
#define SSD1306_OSC_FREQUENCY 8

// Включение встроенного повышающего преобразователя напряжения
// Если схема дисплея использует внешний повышающий преобразователь, установите значение в 0
#define SSD1306_USE_CHARGE_PUMP 1

// Длительность фазы разряда в циклах тактового генератора. Значения 1 - 15. Рекомендуемое значение 2
#define SSD1306_PRECHARGE_PHASE1_DURATION 2

// Длительность фазы заряда высоким напряжением в циклах тактового генератора. Значения 1 - 15. Рекомендуемое значение 2
#define SSD1306_PRECHARGE_PHASE2_DURATION 2


#endif /* SSD1306 */