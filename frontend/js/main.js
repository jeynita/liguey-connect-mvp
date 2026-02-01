/**
 * LIGUEY CONNECT - JavaScript Principal
 * Version: 1.1 (corrigée)
 * Date: Janvier 2026
 */

// ============================================
// CONFIGURATION API
// ============================================

const API_BASE_URL = '/liguey-connect-mvp/api';

// ============================================
// UTILITAIRES
// ============================================

function afficherAlerte(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} fade-in`;
    alertDiv.textContent = message;

    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);

    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 300);
    }, 5000);
}

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
    } else if (overlay) {
        overlay.style.display = 'none';
    }
}

async function requeteAPI(endpoint, options = {}) {
    const url = `${API_BASE_URL}/${endpoint}`;
    const response = await fetch(url, {
        headers: { 'Content-Type': 'application/json' },
        ...options
    });

    return response.json();
}

// ============================================
// AUTHENTIFICATION
// ============================================

function verifierConnexion() {
    const user = localStorage.getItem('liguey_user');
    return user ? JSON.parse(user) : null;
}

function sauvegarderUtilisateur(userData) {
    localStorage.setItem('liguey_user', JSON.stringify(userData));
}

function deconnecterUtilisateur() {
    localStorage.removeItem('liguey_user');
    window.location.href = 'index.html';
}

// ============================================
// HEADER DYNAMIQUE
// ============================================

function mettreAJourHeader() {
    const user = verifierConnexion();
    const navMenu = document.querySelector('nav ul');
    if (!navMenu) return;

    if (user) {
        navMenu.innerHTML = `
            <li><a href="index.html">Accueil</a></li>
            <li><a href="recherche.html">Rechercher</a></li>
            ${user.type_utilisateur === 'recruteur' ? '<li><a href="publier-offre.html">Publier une offre</a></li>' : ''}
            ${user.type_utilisateur === 'prestataire' ? '<li><a href="mon-profil.html">Mon profil</a></li>' : ''}
            <li><a href="messages.html">Messages</a></li>
            <li><a href="#" onclick="deconnecterUtilisateur()">Déconnexion</a></li>
        `;
    } else {
        navMenu.innerHTML = `
            <li><a href="index.html">Accueil</a></li>
            <li><a href="recherche.html">Rechercher</a></li>
            <li><a href="connexion.html">Connexion</a></li>
            <li><a href="inscription.html" class="btn btn-secondary btn-sm">S'inscrire</a></li>
        `;
    }
}

// ============================================
// FORMULAIRES
// ============================================

function validerFormulaire(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        input.style.borderColor = '';
        const error = input.nextElementSibling;
        if (error?.classList.contains('form-error')) error.remove();

        if (!input.value.trim()) {
            isValid = false;
            const err = document.createElement('div');
            err.className = 'form-error';
            err.textContent = 'Ce champ est obligatoire';
            input.after(err);
            input.style.borderColor = 'var(--color-error)';
        }
    });

    return isValid;
}

// ============================================
// MODALS
// ============================================

function ouvrirModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function fermerModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// ============================================
// API MÉTIER
// ============================================

/**
 * INSCRIPTION
 * ➜ crée un compte
 * ➜ NE CONNECTE PAS
 * ➜ redirige vers connexion
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
            afficherAlerte(
                'Compte créé avec succès. Veuillez vous connecter.',
                'success'
            );

            setTimeout(() => {
                window.location.href = 'connexion.html';
            }, 1200);
        } else {
            afficherAlerte(response.message, 'error');
        }

        return response;
    } catch (err) {
        afficherChargement(false);
        afficherAlerte('Erreur serveur', 'error');
        throw err;
    }
}

/**
 * CONNEXION
 * ➜ authentifie
 * ➜ sauvegarde utilisateur
 * ➜ redirige accueil
 */
async function connecterUtilisateur(identifiant, motDePasse) {
    try {
        afficherChargement(true);
        const response = await requeteAPI('connexion.php', {
            method: 'POST',
            body: JSON.stringify({
                identifiant,
                mot_de_passe: motDePasse
            })
        });

        afficherChargement(false);

        if (response.success) {
            sauvegarderUtilisateur(response.data.user);
            afficherAlerte(response.message, 'success');

            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
        } else {
            afficherAlerte(response.message, 'error');
        }

        return response;
    } catch (err) {
        afficherChargement(false);
        afficherAlerte('Erreur serveur', 'error');
        throw err;
    }
}

// ============================================
// INITIALISATION
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    mettreAJourHeader();
});

// ============================================
// EXPORT GLOBAL
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
    validerFormulaire,
    ouvrirModal,
    fermerModal
};
