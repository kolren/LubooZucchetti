interface Seat {
  id: string;
  status: 'available' | 'occupied' | 'selected';
  row: number;
  col: number;
}

interface SeatingGridProps {
  seats: Seat[];
  onSeatClick: (seatId: string) => void;
  selectedStation: string;
}

export function SeatingGrid({ seats, onSeatClick, selectedStation }: SeatingGridProps) {
  const getSeatColor = (status: string) => {
    switch (status) {
      case 'available':
        return 'stroke-[#36A482] fill-transparent';
      case 'occupied':
        return 'stroke-red-500 fill-red-500/20';
      case 'selected':
        return 'stroke-white fill-white/30';
      default:
        return 'stroke-white fill-transparent';
    }
  };

  return (
    <div className="relative h-full w-full bg-[rgba(0,51,128,0.24)] rounded-[34px] p-8">
      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <div className="flex items-center gap-4">
          <button className="px-6 py-2 bg-[rgba(198,101,213,0.6)] rounded-[19px] text-white font-bold text-xl hover:bg-[rgba(198,101,213,0.8)] transition-colors">
            ← Piano 1
          </button>
        </div>
        <h2 className="text-3xl font-bold text-white">{selectedStation}</h2>
      </div>

      {/* Seating Layout */}
      <div className="relative flex flex-col items-center justify-center gap-8 mt-12">
        {/* Top row - 5 seats */}
        <div className="flex gap-6">
          {seats.slice(0, 5).map((seat) => (
            <button
              key={seat.id}
              onClick={() => onSeatClick(seat.id)}
              className={`
                relative w-16 h-16 border-2 rounded-xl transition-all
                hover:scale-110 hover:shadow-lg
                ${getSeatColor(seat.status)}
              `}
            >
              <svg className="w-full h-full" viewBox="0 0 60 60">
                <rect x="5" y="5" width="50" height="50" rx="8" className="stroke-current" strokeWidth="2" fill="currentFill" />
                <path d="M 15 30 Q 30 20, 45 30" className="stroke-current" strokeWidth="2" fill="none" />
                <circle cx="25" cy="25" r="2" className="fill-current" />
                <circle cx="35" cy="25" r="2" className="fill-current" />
              </svg>
            </button>
          ))}
        </div>

        {/* Middle rows - 2x3 */}
        <div className="flex gap-12">
          <div className="flex flex-col gap-6">
            {seats.slice(5, 7).map((seat) => (
              <button
                key={seat.id}
                onClick={() => onSeatClick(seat.id)}
                className={`
                  relative w-16 h-16 border-2 rounded-xl transition-all
                  hover:scale-110 hover:shadow-lg
                  ${getSeatColor(seat.status)}
                `}
              >
                <svg className="w-full h-full" viewBox="0 0 60 60">
                  <rect x="5" y="5" width="50" height="50" rx="8" className="stroke-current" strokeWidth="2" fill="currentFill" />
                  <path d="M 15 30 Q 30 20, 45 30" className="stroke-current" strokeWidth="2" fill="none" />
                  <circle cx="25" cy="25" r="2" className="fill-current" />
                  <circle cx="35" cy="25" r="2" className="fill-current" />
                </svg>
              </button>
            ))}
          </div>
          <div className="flex flex-col gap-6">
            {seats.slice(7, 9).map((seat) => (
              <button
                key={seat.id}
                onClick={() => onSeatClick(seat.id)}
                className={`
                  relative w-16 h-16 border-2 rounded-xl transition-all
                  hover:scale-110 hover:shadow-lg
                  ${getSeatColor(seat.status)}
                `}
              >
                <svg className="w-full h-full" viewBox="0 0 60 60">
                  <rect x="5" y="5" width="50" height="50" rx="8" className="stroke-current" strokeWidth="2" fill="currentFill" />
                  <path d="M 15 30 Q 30 20, 45 30" className="stroke-current" strokeWidth="2" fill="none" />
                  <circle cx="25" cy="25" r="2" className="fill-current" />
                  <circle cx="35" cy="25" r="2" className="fill-current" />
                </svg>
              </button>
            ))}
          </div>
        </div>

        {/* Bottom row - 5 seats */}
        <div className="flex gap-6">
          {seats.slice(9, 14).map((seat) => (
            <button
              key={seat.id}
              onClick={() => onSeatClick(seat.id)}
              className={`
                relative w-16 h-16 border-2 rounded-xl transition-all
                hover:scale-110 hover:shadow-lg
                ${getSeatColor(seat.status)}
              `}
            >
              <svg className="w-full h-full" viewBox="0 0 60 60">
                <rect x="5" y="5" width="50" height="50" rx="8" className="stroke-current" strokeWidth="2" fill="currentFill" />
                <path d="M 15 30 Q 30 20, 45 30" className="stroke-current" strokeWidth="2" fill="none" />
                <circle cx="25" cy="25" r="2" className="fill-current" />
                <circle cx="35" cy="25" r="2" className="fill-current" />
              </svg>
            </button>
          ))}
        </div>
      </div>

      {/* Bottom text with POSTI DISPONIBILI */}
      <div className="absolute bottom-8 right-8">
        <div className="flex items-end gap-4">
          <span className="text-white/60 font-bold text-2xl">POSTI DISPONIBILI</span>
          <span className="text-white font-bold text-7xl">
            {seats.filter(s => s.status === 'available').length}
          </span>
        </div>
      </div>
    </div>
  );
}
