'use client'

import { useState } from 'react'
import { useUser } from '@/contexts/UserContext'
import LoginModal from './LoginModal'
import { motion } from 'framer-motion'
import { Menu } from 'lucide-react'

export default function Header() {
  const { user, logout } = useUser()
  const [showLoginModal, setShowLoginModal] = useState(false)
  const [showMenu, setShowMenu] = useState(false)

  return (
    <header className="fixed top-0 w-full z-50 glass-effect">
      <div className="container mx-auto px-4 py-2 sm:py-4">
        <div className="flex flex-col sm:flex-row items-center justify-between space-y-2 sm:space-y-0">
          <motion.div 
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            className="flex items-center space-x-4 w-full sm:w-auto justify-between"
          >
            <h1 className="text-xl sm:text-2xl font-bold tracking-tighter logo">
              win<span className="text-[#CCFF00]">mix</span>
              <span className="text-xs text-[#CCFF00] ml-1">.hu</span>
            </h1>
            <div className="flex space-x-2 sm:hidden">
              {user ? (
                <button
                  onClick={() => setShowMenu(!showMenu)}
                  className="btn-modern p-2 rounded-full bg-[#CCFF00] text-black"
                >
                  <Menu size={24} />
                </button>
              ) : (
                <button
                  onClick={() => setShowLoginModal(true)}
                  className="btn-modern px-4 py-2 rounded-full bg-[#CCFF00] text-black font-medium hover:bg-[#CCFF00]/90 transition-all text-sm"
                >
                  Bejelentkezés
                </button>
              )}
            </div>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            className="hidden sm:flex items-center space-x-4"
          >
            <button className="btn-modern px-4 py-2 rounded-full bg-gradient-to-r from-[#CCFF00]/20 to-transparent text-[#CCFF00] hover:from-[#CCFF00]/30">
              Mérkőzések kiválasztása
            </button>
            <button className="btn-modern px-4 py-2 rounded-full bg-gradient-to-r from-[#CCFF00]/20 to-transparent text-[#CCFF00] hover:from-[#CCFF00]/30">
              Statisztikák
            </button>
            {user ? (
              <>
                <div className="glass-effect px-4 py-2 rounded-full">
                  <span className="text-[#CCFF00]">👋</span>
                  <span className="ml-2">{user.username}</span>
                </div>
                <button
                  onClick={logout}
                  className="btn-modern px-4 py-2 rounded-full bg-gradient-to-r from-[#CCFF00]/20 to-transparent text-[#CCFF00] hover:from-[#CCFF00]/30"
                >
                  Kijelentkezés
                </button>
              </>
            ) : (
              <button
                onClick={() => setShowLoginModal(true)}
                className="btn-modern px-4 py-2 rounded-full bg-[#CCFF00] text-black font-medium hover:bg-[#CCFF00]/90 transition-all text-sm sm:text-base"
              >
                Bejelentkezés
              </button>
            )}
          </motion.div>
        </div>
      </div>
      {showLoginModal && (
        <LoginModal onClose={() => setShowLoginModal(false)} />
      )}
      {showMenu && (
        <div className="sm:hidden absolute top-full left-0 right-0 bg-black/90 p-4">
          <div className="flex flex-col space-y-2">
            <button className="btn-modern px-4 py-2 rounded-full bg-gradient-to-r from-[#CCFF00]/20 to-transparent text-[#CCFF00] hover:from-[#CCFF00]/30">
              Mérkőzések kiválasztása
            </button>
            <button className="btn-modern px-4 py-2 rounded-full bg-gradient-to-r from-[#CCFF00]/20 to-transparent text-[#CCFF00] hover:from-[#CCFF00]/30">
              Statisztikák
            </button>
            <div className="glass-effect px-4 py-2 rounded-full">
              <span className="text-[#CCFF00]">👋</span>
              <span className="ml-2">{user?.username}</span>
            </div>
            <button
              onClick={logout}
              className="btn-modern px-4 py-2 rounded-full bg-gradient-to-r from-[#CCFF00]/20 to-transparent text-[#CCFF00] hover:from-[#CCFF00]/30"
            >
              Kijelentkezés
            </button>
          </div>
        </div>
      )}
    </header>
  )
}

