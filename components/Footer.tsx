'use client'

import Link from 'next/link'
import { motion } from 'framer-motion'
import { Home, BarChart2, Star, Clock, Settings } from 'lucide-react'

export default function Footer() {
  return (
    <motion.footer
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      className="fixed bottom-0 w-full glass-effect border-t border-white/5 py-2 sm:py-4 px-4 sm:px-6"
    >
      <div className="container mx-auto">
        <nav className="flex justify-around items-end max-w-lg mx-auto">
          {[
            { href: "/", icon: Home, label: "Home" },
            { href: "/statistics", icon: BarChart2, label: "Statisztikák" },
            { href: "/favorites", icon: Star, label: "Kedvencek" },
            { href: "/recent", icon: Clock, label: "Legutóbbi" },
            { href: "/settings", icon: Settings, label: "Beállítások" }
          ].map((item, index) => (
            <Link
              key={index}
              href={item.href}
              className={`flex flex-col items-center space-y-1 transition-colors ${
                index === 0 ? 'text-[#CCFF00]' : 'text-gray-400 hover:text-[#CCFF00]'
              }`}
            >
              <item.icon className="w-5 h-5 sm:w-6 sm:h-6" />
              <span className="text-xs sm:text-sm">{item.label}</span>
            </Link>
          ))}
        </nav>
      </div>
    </motion.footer>
  )
}

