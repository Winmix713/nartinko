'use client'

import { motion } from 'framer-motion'
import { TEAMS } from '@/lib/constants'
import { Trophy, TrendingUp, Activity } from 'lucide-react'

interface PredictionResultsProps {
  predictions: any[]
}

export default function PredictionResults({ predictions }: PredictionResultsProps) {
  const getTeamName = (teamId: string) => TEAMS.find(team => team.id === teamId)?.name || teamId
  const getTeamLogo = (teamId: string) => TEAMS.find(team => team.id === teamId)?.logoUrl || ''

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      className="space-y-6"
    >
      <h2 className="text-2xl font-bold text-[#CCFF00]">Predikciók eredménye</h2>
      
      <div className="grid gap-6 md:grid-cols-2">
        {predictions.map((prediction, index) => (
          <motion.div
            key={index}
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: index * 0.1 }}
            className="glass-effect rounded-2xl p-6 card-hover"
          >
            <div className="flex items-center justify-between mb-6">
              <div className="flex items-center space-x-4">
                <img
                  src={getTeamLogo(prediction.match.homeTeam)}
                  alt="Home team"
                  className="w-12 h-12"
                />
                <div className="text-center">
                  <div className="text-2xl font-bold">
                    {prediction.prediction.homeScore} - {prediction.prediction.awayScore}
                  </div>
                  <div className="text-sm text-gray-400">Prediktált eredmény</div>
                </div>
                <img
                  src={getTeamLogo(prediction.match.awayTeam)}
                  alt="Away team"
                  className="w-12 h-12"
                />
              </div>
              <div className="flex items-center space-x-2">
                <Trophy className="w-5 h-5 text-[#CCFF00]" />
                <span className="text-lg font-medium">
                  {prediction.prediction.confidence}%
                </span>
              </div>
            </div>

            <div className="space-y-4">
              <div className="flex items-center justify-between p-3 bg-white/5 rounded-xl">
                <div className="flex items-center space-x-2">
                  <Activity className="w-4 h-4 text-[#CCFF00]" />
                  <span>Birtoklás</span>
                </div>
                <div className="w-48 h-2 bg-black/30 rounded-full overflow-hidden">
                  <div
                    className="h-full bg-[#CCFF00]"
                    style={{ width: `${prediction.prediction.stats.possession.home}%` }}
                  />
                </div>
                <span className="text-sm">
                  {prediction.prediction.stats.possession.home}%
                </span>
              </div>

              <div className="flex items-center justify-between p-3 bg-white/5 rounded-xl">
                <div className="flex items-center space-x-2">
                  <TrendingUp className="w-4 h-4 text-[#CCFF00]" />
                  <span>Forma</span>
                </div>
                <div className="flex space-x-2">
                  <span className="text-sm">{prediction.prediction.stats.form.home}</span>
                  <span className="text-gray-400">vs</span>
                  <span className="text-sm">{prediction.prediction.stats.form.away}</span>
                </div>
              </div>
            </div>

            <div className="mt-6 pt-4 border-t border-white/10">
              <div className="flex justify-between text-sm text-gray-400">
                <span>Predikció pontszáma:</span>
                <span className="text-[#CCFF00]">
                  {prediction.prediction.predictionScore.toFixed(2)}
                </span>
              </div>
            </div>
          </motion.div>
        ))}
      </div>
    </motion.div>
  )
}

