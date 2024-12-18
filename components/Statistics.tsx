'use client'

import { motion } from 'framer-motion'
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts'

interface StatisticsProps {
  predictions: any[]
}

const COLORS = ['#CCFF00', '#00C49F', '#FFBB28', '#FF8042', '#0088FE'];

export default function Statistics({ predictions }: StatisticsProps) {
  const confidenceData = predictions.map(pred => ({
    name: `${pred.match.homeTeam} vs ${pred.match.awayTeam}`,
    confidence: pred.prediction.confidence
  }));

  const outcomeData = predictions.reduce((acc, pred) => {
    const outcome = pred.prediction.homeScore > pred.prediction.awayScore ? 'Home Win' :
                    pred.prediction.homeScore < pred.prediction.awayScore ? 'Away Win' : 'Draw';
    acc[outcome] = (acc[outcome] || 0) + 1;
    return acc;
  }, {});

  const pieData = Object.entries(outcomeData).map(([name, value]) => ({ name, value }));

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      className="space-y-8"
    >
      <h2 className="text-2xl font-bold text-[#CCFF00]">Statisztikák</h2>

      <div className="grid gap-6 md:grid-cols-2">
        <motion.div
          initial={{ opacity: 0, scale: 0.95 }}
          animate={{ opacity: 1, scale: 1 }}
          className="glass-effect rounded-2xl p-6"
        >
          <h3 className="text-lg font-medium mb-4">Magabiztosság eloszlása</h3>
          <div className="h-[200px]">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={confidenceData}>
                <XAxis dataKey="name" tick={false} />
                <YAxis tick={{ fill: '#ffffff', fontSize: 12 }} />
                <Tooltip
                  contentStyle={{
                    backgroundColor: '#1a1a1a',
                    border: '1px solid rgba(255, 255, 255, 0.1)',
                    borderRadius: '8px'
                  }}
                />
                <Bar dataKey="confidence" fill="#CCFF00" radius={[4, 4, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </motion.div>

        <motion.div
          initial={{ opacity: 0, scale: 0.95 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ delay: 0.1 }}
          className="glass-effect rounded-2xl p-6"
        >
          <h3 className="text-lg font-medium mb-4">Eredmények megoszlása</h3>
          <div className="h-[200px]">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={pieData}
                  cx="50%"
                  cy="50%"
                  innerRadius={60}
                  outerRadius={80}
                  fill="#8884d8"
                  paddingAngle={5}
                  dataKey="value"
                >
                  {pieData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip
                  contentStyle={{
                    backgroundColor: '#1a1a1a',
                    border: '1px solid rgba(255, 255, 255, 0.1)',
                    borderRadius: '8px'
                  }}
                />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </motion.div>
      </div>

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2 }}
        className="glass-effect rounded-2xl p-6"
      >
        <h3 className="text-lg font-medium mb-4">Összesített statisztikák</h3>
        <div className="grid gap-4 md:grid-cols-3">
          <div>
            <p className="text-sm text-gray-400">Összes predikció</p>
            <p className="text-2xl font-bold">{predictions.length}</p>
          </div>
          <div>
            <p className="text-sm text-gray-400">Átlagos magabiztosság</p>
            <p className="text-2xl font-bold">
              {Math.round(predictions.reduce((sum, pred) => sum + pred.prediction.confidence, 0) / predictions.length || 0)}%
            </p>
          </div>
          <div>
            <p className="text-sm text-gray-400">Legmagasabb pontszám</p>
            <p className="text-2xl font-bold">
              {Math.max(...predictions.map(pred => pred.prediction.predictionScore)).toFixed(2)}
            </p>
          </div>
        </div>
      </motion.div>
    </motion.div>
  )
}

