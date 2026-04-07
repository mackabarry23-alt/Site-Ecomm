<?php
declare(strict_types=1);
?>
  <footer id="contact">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>Lueur d'Ambre</h3>
          <p>Des bougies parfumees pensees pour apporter chaleur, douceur et caractere a chaque interieur.</p>
        </div>

        <div class="footer-section">
          <h3>Navigation</h3>
          <ul>
            <li><a href="/page/index.php">Accueil</a></li>
            <li><a href="/page/catalogue-bougies.php">Catalogue</a></li>
            <li><a href="/page/panier.php">Panier</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h3>Contact</h3>
          <ul>
            <li><a href="mailto:contact@lueurdambre.be">contact@lueurdambre.be</a></li>
            <li>+32 470 00 00 00</li>
            <li>Bruxelles, Belgique</li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Lueur d'Ambre - Tous droits reserves.</p>
      </div>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    function toCartCountNumber(count) {
      const parsedCount = Number.parseInt(count, 10);

      return Number.isFinite(parsedCount) && parsedCount >= 0 ? parsedCount : 0;
    }

    function formatCartCountLabel(count) {
      const safeCount = toCartCountNumber(count);

      return safeCount + ' article' + (safeCount > 1 ? 's' : '');
    }

    function updateCartCounters(count) {
      const safeCount = toCartCountNumber(count);

      document.querySelectorAll('[data-cart-count]').forEach((element) => {
        element.textContent = safeCount;
      });
    }

    function updateCartCountLabels(count) {
      const label = formatCartCountLabel(count);

      document.querySelectorAll('[data-cart-count-label]').forEach((element) => {
        element.textContent = label;
      });
    }

    function updateCartDisplays(count) {
      updateCartCounters(count);
      updateCartCountLabels(count);
    }

    function updateCartTotals(formattedTotal, formattedTotalTvac = null) {
      document.querySelectorAll('[data-cart-total]').forEach((element) => {
        element.textContent = formattedTotal;
      });

      if (formattedTotalTvac !== null) {
        document.querySelectorAll('[data-cart-total-tvac]').forEach((element) => {
          element.textContent = formattedTotalTvac;
        });
      }
    }

    function toggleCartEmptyState(isEmpty) {
      document.querySelectorAll('[data-cart-empty]').forEach((element) => {
        element.hidden = !isEmpty;
      });

      document.querySelectorAll('[data-cart-lines]').forEach((element) => {
        element.hidden = isEmpty;
      });

      document.querySelectorAll('[data-checkout-button]').forEach((element) => {
        element.disabled = isEmpty;
      });
    }

    function showCartFeedback(message, isError = false) {
      document.querySelectorAll('[data-cart-feedback]').forEach((element) => {
        element.hidden = false;
        element.textContent = message;
        element.dataset.state = isError ? 'error' : 'success';

        window.clearTimeout(element._feedbackTimer);
        element._feedbackTimer = window.setTimeout(() => {
          element.hidden = true;
          element.textContent = '';
        }, 2200);
      });
    }

    function normalizeCartQuantity(quantity) {
      const parsedQuantity = Number.parseInt(quantity, 10);

      return Number.isFinite(parsedQuantity) && parsedQuantity > 0 ? parsedQuantity : 1;
    }

    function normalizeEditableCartQuantity(quantity, maxQuantity = Number.MAX_SAFE_INTEGER) {
      const parsedQuantity = Number.parseInt(quantity, 10);
      const safeMaxQuantity = Math.max(0, Number.parseInt(maxQuantity, 10) || 0);

      if (!Number.isFinite(parsedQuantity)) {
        return 0;
      }

      return Math.min(Math.max(parsedQuantity, 0), safeMaxQuantity);
    }

    function resetCartButtonLabel(button) {
      if (!button) {
        return;
      }

      window.setTimeout(() => {
        button.textContent = button.dataset.defaultLabel || 'Ajouter au panier';
      }, 1500);
    }

    function addToCart(productId, quantity = 1, button = null) {
      const safeQuantity = normalizeCartQuantity(quantity);

      if (typeof axios === 'undefined') {
        showCartFeedback('Le service panier est indisponible pour le moment.', true);
        return;
      }

      if (button) {
        button.disabled = true;
      }

      axios.postForm('/page/actions/ajouter-panier.php', {
        product_id: productId,
        quantity: safeQuantity
      })
        .then((response) => {
          const count = String(response.data).trim();

          updateCartDisplays(count);
          showCartFeedback('Bougie ajoutee au panier.');

          if (button) {
            button.textContent = 'Ajoute au panier';
          }
        })
        .catch(() => {
          showCartFeedback("Impossible d'ajouter cette bougie pour le moment.", true);
        })
        .finally(() => {
          if (button) {
            button.disabled = false;
            resetCartButtonLabel(button);
          }
        });
    }

    function addToCartFromInput(productId, inputId, button = null) {
      const input = document.getElementById(inputId);
      const quantity = input ? input.value : 1;

      addToCart(productId, quantity, button);
    }

    function getCartQuantityInput(productId) {
      return document.querySelector('[data-cart-input="' + productId + '"]');
    }

    function setCartLineBusy(productId, busy) {
      document.querySelectorAll('[data-cart-control="' + productId + '"]').forEach((element) => {
        element.disabled = busy;
      });
    }

    function changeCartQuantity(productId, delta) {
      const input = getCartQuantityInput(productId);

      if (!input) {
        return;
      }

      const nextValue = normalizeEditableCartQuantity(
        (Number.parseInt(input.value, 10) || 0) + delta,
        input.max
      );

      input.value = nextValue;
      submitCartQuantity(productId, nextValue);
    }

    function applyCartLineUpdate(productId, payload) {
      updateCartDisplays(payload.cart_count);
      updateCartTotals(payload.cart_total_formatted, payload.cart_total_tvac_formatted);
      toggleCartEmptyState(Boolean(payload.empty));

      const line = document.querySelector('[data-cart-line="' + productId + '"]');

      if (!line) {
        return;
      }

      if (payload.removed) {
        line.remove();
        return;
      }

      const input = getCartQuantityInput(productId);

      if (input) {
        input.value = payload.quantity;
        input.max = payload.stock;
        input.dataset.currentValue = payload.quantity;
      }

      const lineTotal = document.querySelector('[data-line-total="' + productId + '"]');

      if (lineTotal) {
        lineTotal.textContent = payload.line_total_formatted;
      }
    }

    function submitCartQuantity(productId, quantity) {
      if (typeof axios === 'undefined') {
        showCartFeedback('Le service panier est indisponible pour le moment.', true);
        return;
      }

      const input = getCartQuantityInput(productId);
      const previousValue = input ? input.dataset.currentValue || input.value : '0';
      const safeQuantity = input
        ? normalizeEditableCartQuantity(quantity, input.max)
        : Math.max(0, Number.parseInt(quantity, 10) || 0);

      if (input) {
        input.value = safeQuantity;
      }

      setCartLineBusy(productId, true);

      axios.postForm('/page/actions/modifier-panier.php', {
        product_id: productId,
        quantity: safeQuantity
      })
        .then((response) => {
          const payload = response.data || {};

          applyCartLineUpdate(productId, payload);
          showCartFeedback(payload.message || 'Panier mis a jour.');
        })
        .catch((error) => {
          if (input) {
            input.value = previousValue;
          }

          const message = error && error.response && error.response.data && error.response.data.message
            ? error.response.data.message
            : 'Impossible de mettre a jour la quantite pour le moment.';

          showCartFeedback(message, true);
        })
        .finally(() => {
          setCartLineBusy(productId, false);
        });
    }
  </script>
</body>
</html>
