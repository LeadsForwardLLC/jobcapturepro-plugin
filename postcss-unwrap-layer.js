/**
 * PostCSS plugin to remove @layer wrappers from Tailwind v4 output.
 *
 * Un-layered CSS always beats @layer-ed CSS in the cascade, so themes
 * like Oxygen Builder override every Tailwind rule. Stripping the
 * @layer wrappers puts our styles on equal footing.
 */
module.exports = () => ({
  postcssPlugin: 'postcss-unwrap-layer',
  AtRule: {
    layer(atRule) {
      if (atRule.nodes && atRule.nodes.length) {
        atRule.replaceWith(atRule.nodes);
      } else {
        atRule.remove();
      }
    }
  }
});
module.exports.postcss = true;
