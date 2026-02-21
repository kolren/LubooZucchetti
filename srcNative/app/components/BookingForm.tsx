interface BookingFormProps {
  selectedSeat: string | null;
  selectedStation: string;
  stationDescription: string;
  onConfirm: () => void;
}

export function BookingForm({ 
  selectedSeat, 
  selectedStation, 
  stationDescription,
  onConfirm 
}: BookingFormProps) {
  return (
    <div className="relative h-full w-96 bg-[rgba(0,51,128,0.24)] rounded-[34px] p-6">
      <h2 className="text-2xl font-bold text-white mb-8">
        Conferma prenotazione
      </h2>

      <div className="space-y-6">
        {/* Posizione */}
        <div>
          <label className="text-white/70 text-sm mb-2 block">Posizione</label>
          <div className="bg-[rgba(99,144,180,0.84)] rounded-xl px-4 py-3">
            <span className="text-white font-black text-2xl">
              {selectedSeat || 'A1'}
            </span>
          </div>
        </div>

        {/* Armadietto */}
        <div>
          <label className="text-white/70 text-sm mb-2 block">Armadietto</label>
          <div className="bg-[rgba(99,165,180,0.84)] rounded-xl px-4 py-3">
            <span className="text-white font-black text-2xl">Aa1</span>
          </div>
        </div>

        {/* Data */}
        <div>
          <label className="text-white/70 text-sm mb-2 block">Data</label>
          <input 
            type="date" 
            defaultValue="2026-01-17"
            className="w-full bg-[rgba(99,144,180,0.84)] rounded-xl px-4 py-3 text-white text-xl font-light focus:outline-none focus:ring-2 focus:ring-white/50"
          />
        </div>

        {/* Orari */}
        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="text-white/70 text-sm mb-2 block">Inizio</label>
            <input 
              type="time" 
              defaultValue="09:00"
              className="w-full bg-[rgba(99,144,180,0.84)] rounded-xl px-4 py-3 text-white text-xl font-light focus:outline-none focus:ring-2 focus:ring-white/50"
            />
          </div>
          <div>
            <label className="text-white/70 text-sm mb-2 block">Fine</label>
            <input 
              type="time" 
              defaultValue="18:00"
              className="w-full bg-[rgba(99,144,180,0.84)] rounded-xl px-4 py-3 text-white text-xl font-light focus:outline-none focus:ring-2 focus:ring-white/50"
            />
          </div>
        </div>

        {/* Station Info */}
        <div className="bg-[rgba(192,199,255,0.55)] rounded-2xl p-4">
          <div className="flex items-start gap-3">
            <div className="text-2xl">🖥️</div>
            <div>
              <div className="text-white font-bold text-base">{selectedStation}</div>
              <div className="text-white/80 text-xs">{stationDescription}</div>
            </div>
          </div>
        </div>

        {/* Confirm Button */}
        <button 
          onClick={onConfirm}
          disabled={!selectedSeat}
          className={`
            w-full py-3 px-6 rounded-xl font-semibold text-white transition-all
            ${selectedSeat 
              ? 'bg-[rgba(180,123,99,0.84)] hover:bg-[rgba(180,123,99,1)] hover:scale-105' 
              : 'bg-gray-500/50 cursor-not-allowed'
            }
          `}
        >
          Conferma
        </button>
      </div>
    </div>
  );
}
