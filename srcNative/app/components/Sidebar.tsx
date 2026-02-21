interface Station {
  id: string;
  name: string;
  description: string;
  icon: string;
  isSelected: boolean;
}

interface SidebarProps {
  stations: Station[];
  onStationSelect: (id: string) => void;
}

export function Sidebar({ stations, onStationSelect }: SidebarProps) {
  return (
    <div className="relative h-full w-80 bg-[rgba(0,51,128,0.24)] rounded-[34px] p-6">
      {/* Title */}
      <div className="mb-8">
        <h2 
          className="text-2xl font-bold text-center bg-gradient-to-r from-white to-[#ADD0FF] bg-clip-text text-transparent"
        >
          Prenotazioni disponibili
        </h2>
      </div>
      
      {/* Stations List */}
      <div className="flex flex-col gap-3 mb-12">
        {stations.map((station) => (
          <button
            key={station.id}
            onClick={() => onStationSelect(station.id)}
            className={`
              relative p-4 rounded-2xl transition-all
              ${station.isSelected 
                ? 'bg-gradient-to-r from-[#3f8718] to-[#5fa831]' 
                : 'bg-[rgba(255,255,255,0.1)] hover:bg-[rgba(255,255,255,0.15)]'
              }
            `}
          >
            <div className="flex items-start gap-3">
              <div className="text-3xl">{station.icon}</div>
              <div className="flex-1 text-left">
                <div className="text-white font-bold text-lg">{station.name}</div>
                <div className="text-white text-xs opacity-80">{station.description}</div>
              </div>
            </div>
          </button>
        ))}
      </div>
      
      {/* Legend */}
      <div className="mt-auto">
        <h3 className="text-base font-bold bg-gradient-to-r from-white to-[#ADD0FF] bg-clip-text text-transparent mb-3">
          Legenda
        </h3>
        <div className="flex flex-col gap-2 text-sm">
          <div className="flex items-center gap-3">
            <div className="w-4 h-4 bg-[#0dff00] rounded-full" />
            <span className="text-white font-light">Disponibile</span>
          </div>
          <div className="flex items-center gap-3">
            <div className="w-4 h-4 bg-[#ff0004] rounded-full" />
            <span className="text-white font-light">Occupato</span>
          </div>
          <div className="flex items-center gap-3">
            <div className="w-4 h-4 bg-white rounded-full" />
            <span className="text-white font-light">Selezione</span>
          </div>
        </div>
      </div>
    </div>
  );
}
