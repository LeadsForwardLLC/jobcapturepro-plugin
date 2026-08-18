import {
    createElement,
    Calendar,
    MapPin,
    ChevronLeft,
    ChevronRight,
    Star,
    User,
    Zap,
    CircleCheck,
} from 'lucide';

export function lucideSvg(icon, size, cssClass) {
    const el = createElement(icon, { width: size, height: size });
    if (cssClass) el.setAttribute('class', cssClass);
    el.setAttribute('aria-hidden', 'true');
    return el.outerHTML;
}

export { Calendar, MapPin, ChevronLeft, ChevronRight, Star, User, Zap, CircleCheck };
