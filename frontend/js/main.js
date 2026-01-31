/**
 * LIGUEY CONNECT - JavaScript Principal
 * Version: 1.0
 * Date: Janvier 2026
 */

// Configuration API
const API_BASE_URL = '/liguey-connect-mvp/api';

// ============================================
// UTILITAIRES
// ============================================

/**
 * Afficher un message d'alerte
 */
function afficherAlerte(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} fade-in`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-masquer après 5 secondes
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 300);
    }, 5000);
}

/**
 * Afficher/masquer le spinner de chargement
 */
function afficherChargement(afficher = true) {
    let overlay = document.getElementById('loading-overlay');
    
    if (afficher) {
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class="spinner"></div>';
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    } else {
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
}

/**
 * Requête API générique
 */
async function requeteAPI(endpoint, options = {}) {
    try {
        const url = `${API_BASE_URL}/${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };
        
        const response = await fetch(url, { ...defaultOptions, ...options });
        const data = await response.json();
        
        return data;
    } catch (error) {
        console.error('Erreur API:', error);
        throw error;
    }
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function verifierConnexion() {
    const user = localStorage.getItem('liguey_user');
    return user ? JSON.parse(user) : null;
}

/**
 * Sauvegarder les données utilisateur
 */
function sauvegarderUtilisateur(userData) {
    localStorage.setItem('liguey_user', JSON.stringify(userData));
}

/**
 * Déconnecter l'utilisateur
 */
function deconnecterUtilisateur() {
    localStorage.removeItem('liguey_user');
    window.location.href = 'index.html';
}

/**
 * Mettre à jour le header selon l'état de connexion
 */
function mettreAJourHeader() {
    const user = verifierConnexion();
    const navMenu = document.querySelector('nav ul');
    
    if (!navMenu) return;
    
    if (user) {
        // Utilisateur connecté
        navMenu.innerHTML = `
            <li><a href="index.html">Accueil</a></li>
            <li><a href="recherche.html">Rechercher</a></li>
            ${user.type_utilisateur === 'recruteur' ? '<li><a href="publier-offre.html">Publier une offre</a></li>' : ''}
            ${user.type_utilisateur === 'prestataire' ? '<li><a href="mon-profil.html">Mon profil</a></li>' : ''}
            <li><a href="messages.html">Messages</a></li>
            <li><a href="#" onclick="deconnecterUtilisateur()">Déconnexion</a></li>
        `;
    } else {
        // Utilisateur non connecté
        navMenu.innerHTML = `
            <li><a href="index.html">Accueil</a></li>
            <li><a href="recherche.html">Rechercher</a></li>
            <li><a href="connexion.html">Connexion</a></li>
            <li><a href="inscription.html" class="btn btn-secondary btn-sm">S'inscrire</a></li>
        `;
    }
}

/**
 * Toggle menu mobile
 */
function toggleMenu() {
    const nav = document.querySelector('nav');
    nav.classList.toggle('active');
}

/**
 * Formater la date
 */
function formaterDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('fr-FR', options);
}

/**
 * Générer des étoiles de notation
 */
function genererEtoiles(note, maxEtoiles = 5) {
    let html = '<div class="rating">';
    
    for (let i = 1; i <= maxEtoiles; i++) {
        if (i <= note) {
            html += '<span class="rating-star">★</span>';
        } else {
            html += '<span class="rating-star empty">★</span>';
        }
    }
    
    html += `<span class="rating-text">${note.toFixed(1)}</span>`;
    html += '</div>';
    
    return html;
}

/**
 * Valider un formulaire
 */
function validerFormulaire(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        const errorDiv = input.nextElementSibling;
        if (errorDiv && errorDiv.classList.contains('form-error')) {
            errorDiv.remove();
        }
        
        if (!input.value.trim()) {
            isValid = false;
            const error = document.createElement('div');
            error.className = 'form-error';
            error.textContent = 'Ce champ est obligatoire';
            input.parentNode.insertBefore(error, input.nextSibling);
            input.style.borderColor = 'var(--color-error)';
        } else {
            input.style.borderColor = '';
        }
    });
    
    // Validation email
    const emailInput = form.querySelector('input[type="email"]');
    if (emailInput && emailInput.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            isValid = false;
            const error = document.createElement('div');
            error.className = 'form-error';
            error.textContent = 'Format d\'email invalide';
            emailInput.parentNode.insertBefore(error, emailInput.nextSibling);
            emailInput.style.borderColor = 'var(--color-error)';
        }
    }
    
    // Validation téléphone
    const telInput = form.querySelector('input[type="tel"]');
    if (telInput && telInput.value) {
        const telRegex = /^[0-9\s\+\-]{8,20}$/;
        if (!telRegex.test(telInput.value)) {
            isValid = false;
            const error = document.createElement('div');
            error.className = 'form-error';
            error.textContent = 'Format de téléphone invalide';
            telInput.parentNode.insertBefore(error, telInput.nextSibling);
            telInput.style.borderColor = 'var(--color-error)';
        }
    }
    
    return isValid;
}

// ============================================
// GESTION DES MODALS
// ============================================

function ouvrirModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function fermerModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Fermer modal en cliquant en dehors
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// ============================================
// FONCTIONS API SPÉCIFIQUES
// ============================================

/**
 * Inscription utilisateur
 */
async function inscrireUtilisateur(formData) {
    try {
        afficherChargement(true);
        
        const response = await requeteAPI('inscription.php', {
            method: 'POST',
            body: JSON.stringify(formData)
        });
        
        afficherChargement(false);
        
        if (response.success) {
            sauvegarderUtilisateur(response.data);
            afficherAlerte(response.message, 'success');
            
            // Rediriger après 1 seconde
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
        } else {
            afficherAlerte(response.message, 'error');
        }
        
        return response;
    } catch (error) {
        afficherChargement(false);
        afficherAlerte('Erreur de connexion au serveur', 'error');
        throw error;
    }
}

/**
 * Connexion utilisateur
 */
async function connecterUtilisateur(identifiant, motDePasse) {
    try {
        afficherChargement(true);
        
        const response = await requeteAPI('connexion.php', {
            method: 'POST',
            body: JSON.stringify({
                identifiant: identifiant,
                mot_de_passe: motDePasse
            })
        });
        
        afficherChargement(false);
        
        if (response.success) {
            sauvegarderUtilisateur(response.data.user);
            afficherAlerte(response.message, 'success');
            
            // Rediriger après 1 seconde
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
        } else {
            afficherAlerte(response.message, 'error');
        }
        
        return response;
    } catch (error) {
        afficherChargement(false);
        afficherAlerte('Erreur de connexion au serveur', 'error');
        throw error;
    }
}

/**
 * Récupérer les offres d'emploi
 */
async function recupererOffres(filtres = {}) {
    try {
        const params = new URLSearchParams(filtres);
        const response = await requeteAPI(`offres.php?${params}`);
        return response;
    } catch (error) {
        afficherAlerte('Erreur lors du chargement des offres', 'error');
        throw error;
    }
}

/**
 * Rechercher des prestataires
 */
async function rechercherPrestataires(filtres = {}) {
    try {
        filtres.type = 'prestataires';
        const params = new URLSearchParams(filtres);
        const response = await requeteAPI(`recherche.php?${params}`);
        return response;
    } catch (error) {
        afficherAlerte('Erreur lors de la recherche', 'error');
        throw error;
    }
}

/**
 * Publier une offre d'emploi
 */
async function publierOffre(offreData) {
    try {
        afficherChargement(true);
        
        const response = await requeteAPI('offres.php', {
            method: 'POST',
            body: JSON.stringify(offreData)
        });
        
        afficherChargement(false);
        
        if (response.success) {
            afficherAlerte(response.message, 'success');
            setTimeout(() => {
                window.location.href = 'mes-offres.html';
            }, 1000);
        } else {
            afficherAlerte(response.message, 'error');
        }
        
        return response;
    } catch (error) {
        afficherChargement(false);
        afficherAlerte('Erreur lors de la publication', 'error');
        throw error;
    }
}

// ============================================
// INITIALISATION AU CHARGEMENT DE LA PAGE
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Mettre à jour le header
    mettreAJourHeader();
    
    // Ajouter le bouton toggle menu mobile
    const header = document.querySelector('header .header-container');
    if (header && !document.querySelector('.nav-toggle')) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'nav-toggle';
        toggleBtn.innerHTML = '☰';
        toggleBtn.onclick = toggleMenu;
        header.appendChild(toggleBtn);
    }
    
    // Smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});

// ============================================
// EXPORT DES FONCTIONS (pour utilisation dans d'autres fichiers)
// ============================================

window.LigueyConnect = {
    afficherAlerte,
    afficherChargement,
    requeteAPI,
    verifierConnexion,
    sauvegarderUtilisateur,
    deconnecterUtilisateur,
    mettreAJourHeader,
    inscrireUtilisateur,
    connecterUtilisateur,
    recupererOffres,
    rechercherPrestataires,
    publierOffre,
    formaterDate,
    genererEtoiles,
    validerFormulaire,
    ouvrirModal,
    fermerModal
};
